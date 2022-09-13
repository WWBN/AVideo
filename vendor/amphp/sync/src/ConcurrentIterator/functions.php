<?php

namespace Amp\Sync\ConcurrentIterator;

use Amp\CancelledException;
use Amp\Iterator;
use Amp\Producer;
use Amp\Promise;
use Amp\Sync\Barrier;
use Amp\Sync\Lock;
use Amp\Sync\Semaphore;
use function Amp\asyncCall;
use function Amp\call;
use function Amp\coroutine;

/**
 * Concurrently act on iterator values using {@code $processor}.
 *
 * @param Iterator  $iterator Values to process.
 * @param Semaphore $semaphore Semaphore limiting the concurrency, e.g. {@code LocalSemaphore}
 * @param callable  $processor Processing callable, which is run as coroutine. It should not throw any errors,
 *     otherwise the entire operation is aborted.
 *
 * @return Iterator Result values.
 */
function transform(Iterator $iterator, Semaphore $semaphore, callable $processor): Iterator
{
    return new Producer(static function (callable $emit) use ($iterator, $semaphore, $processor) {
        // one dummy item, because we can't start the barrier with a count of zero
        $barrier = new Barrier(1);

        /** @var \Throwable|null $error */
        $error = null;
        $locks = [];
        $gc = false;

        $processor = coroutine($processor);
        $processor = static function (Lock $lock, $currentElement) use (
            $processor,
            $emit,
            $barrier,
            &$locks,
            &$error,
            &$gc
        ) {
            $done = false;

            try {
                yield $processor($currentElement, $emit);

                $done = true;
            } catch (\Throwable $e) {
                $error = $error ?? $e;
                $done = true;
            } finally {
                if (!$done) {
                    $gc = true;
                }

                unset($locks[$lock->getId()]);

                $lock->release();
                $barrier->arrive();
            }
        };

        while (yield $iterator->advance()) {
            if ($error) {
                break;
            }

            /** @var Lock $lock */
            $lock = yield $semaphore->acquire();
            if ($gc || isset($locks[$lock->getId()])) {
                // Throwing here causes a segfault on PHP 7.3
                return; // throw new CancelledException; // producer and locks have been GCed
            }

            $locks[$lock->getId()] = true;
            $barrier->register();

            asyncCall($processor, $lock, $iterator->getCurrent());
        }

        $barrier->arrive(); // remove dummy item
        yield $barrier->await();

        if ($error) {
            throw $error;
        }
    });
}

/**
 * Concurrently map all iterator values using {@code $processor}.
 *
 * The order of the items in the resulting iterator is not guaranteed in any way.
 *
 * @param Iterator  $iterator Values to map.
 * @param Semaphore $semaphore Semaphore limiting the concurrency, e.g. {@code LocalSemaphore}
 * @param callable  $processor Processing callable, which is run as coroutine. It should not throw any errors,
 *     otherwise the entire operation is aborted.
 *
 * @return Iterator Mapped values.
 */
function map(Iterator $iterator, Semaphore $semaphore, callable $processor): Iterator
{
    $processor = coroutine($processor);

    return transform($iterator, $semaphore, static function ($value, callable $emit) use ($processor) {
        $value = yield $processor($value);

        yield $emit($value);
    });
}

/**
 * Concurrently filter all iterator values using {@code $filter}.
 *
 * The order of the items in the resulting iterator is not guaranteed in any way.
 *
 * @param Iterator  $iterator Values to map.
 * @param Semaphore $semaphore Semaphore limiting the concurrency, e.g. {@code LocalSemaphore}
 * @param callable  $filter Processing callable, which is run as coroutine. It should not throw any errors,
 *     otherwise the entire operation is aborted. Must resolve to a boolean, true to keep values in the resulting
 *     iterator.
 *
 * @return Iterator Values, where {@code $filter} resolved to {@code true}.
 */
function filter(Iterator $iterator, Semaphore $semaphore, callable $filter): Iterator
{
    $filter = coroutine($filter);

    return transform($iterator, $semaphore, static function ($value, callable $emit) use ($filter) {
        $keep = yield $filter($value);
        if (!\is_bool($keep)) {
            throw new \TypeError(__NAMESPACE__ . '\filter\'s callable must resolve to a boolean value, got ' . \gettype($keep));
        }

        if ($keep) {
            yield $emit($value);
        }
    });
}

/**
 * Concurrently invoke a callback on all iterator values using {@code $processor}.
 *
 * @param Iterator  $iterator Values to act on.
 * @param Semaphore $semaphore Semaphore limiting the concurrency, e.g. {@code LocalSemaphore}
 * @param callable  $processor Processing callable, which is run as coroutine. It should not throw any errors,
 *     otherwise the entire operation is aborted.
 *
 * @return Promise
 */
function each(Iterator $iterator, Semaphore $semaphore, callable $processor): Promise
{
    $processor = coroutine($processor);

    $iterator = transform(
        $iterator,
        $semaphore,
        static function ($value, callable $emit) use ($processor) {
            yield $processor($value);
            yield $emit(null);
        }
    );

    // Use Amp\Iterator\discard in the future
    return call(static function () use ($iterator) {
        $count = 0;

        while (yield $iterator->advance()) {
            $count++;
        }

        return $count;
    });
}
