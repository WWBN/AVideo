---
title: Concurrent Iterators
permalink: /concurrent-iterator
---
As already stated in the [preamble of our documentation](https://amphp.org/amp/), the weak link when managing concurrency is humans; so `amphp/sync` provides abstractions to hide the complexity of concurrent iteration.

## Concurrency Approaches

Given you have a list of URLs you want to crawl, let's discuss a few possible approaches. For simplicity, we will assume a `fetch` function already exists, which takes a URL and returns the HTTP status code (which is everything we want to know for these examples).

### Approach 1: Sequential

Simple loop using non-blocking I/O, but no concurrency while fetching the individual URLs; starts the second request as soon as the first completed.

```php
$urls = [...];

Amp\call(function () use ($urls) {
    $results = [];

    foreach ($urls as $url) {
        // always wait for the promise to resolve before fetching the next one
        $statusCode = yield fetch($url);
        $results[$url] = $statusCode;
    }
    
    return $results;
});
```

### Approach 2: Everything Concurrently

Almost the same loop, but awaiting all promises at once; starts all requests immediately. Might not be feasible with too many URLs.

```php
$urls = [...];

Amp\call(function () use ($urls) {
    $results = [];

    foreach ($urls as $url) {
        // note the missing yield, we're adding the promises to the array
        $statusCodePromise = fetch($url);
        $results[$url] = $statusCodePromise;
    }

    // yielding an array of promises awaits them all at once
    $results = yield $results;
    
    return $results;
});
```

### Approach 3: Concurrent Chunks

Splitting the jobs into chunks of ten; all requests within a chunk are made concurrently, but each chunk sequentially, so the timing for each chunk depends on the slowest response; starts the eleventh request as soon as the first ten requests completed.

```php
$urls = [...];

Amp\call(function () use ($urls) {
    $results = [];

    foreach (\array_chunk($urls, 10) as $chunk) {
        $promises = [];

        foreach ($chunk as $url) {
            // note the missing yield, we're adding the promises to the array
            $statusCodePromise = fetch($url);
            $promises[$url] = $statusCodePromise;
        }

        // yielding an array of promises awaits them all at once
        $results = \array_merge($results, yield $promises);
    }
    
    return $results;
});
```

### Approach 4: Concurrent Iterator

Concurrent iteration, keeping the concurrency at a maximum of ten; starts the eleventh request as soon as any of the first ten requests completes.

```php
$urls = [...];

Amp\call(function () use ($urls) {
    $results = [];

    yield Amp\Sync\ConcurrentIterator\each(
        Amp\Iterator\fromIterable($urls),
        new Amp\Sync\LocalSemaphore(10),
        function (string $url) use (&$results) {
            $statusCode = yield fetch($url);
            $results[$url] = $statusCode;
        }
    );

    return $results;
});
```

## Provided APIs

### `Amp\Sync\ConcurrentIterator\each`

Calls `$processor` for each item in the iterator while acquiring a lock from `$semaphore` during each operation.
The returned `Promise` resolves as soon as the iterator is empty and all operations are completed.

Use `LocalSemaphore` if you don't need to synchronize beyond a single process.

```php
function each(Iterator $iterator, Semaphore $semaphore, callable $processor): Promise
{
    // ...
}
```

### `Amp\Sync\ConcurrentIterator\map`

Calls `$processor` for each item in the iterator while acquiring a lock from `$semaphore` during each operation.
Returns a new `Iterator` instance with the return values of `$processor`.
 
Use `LocalSemaphore` if you don't need to synchronize beyond a single process.

```php
function map(Iterator $iterator, Semaphore $semaphore, callable $processor): Iterator
{
    // ...
}
```

### `Amp\Sync\ConcurrentIterator\filter`

Calls `$filter` for each item in the iterator while acquiring a lock from `$semaphore` during each operation.
Returns a new `Iterator` instance with the original values where `$filter` resolves to `true`.
 
Use `LocalSemaphore` if you don't need to synchronize beyond a single process.

```php
function filter(Iterator $iterator, Semaphore $semaphore, callable $filter): Iterator
{
    // ...
}
```

### `Amp\Sync\ConcurrentIterator\transform`

Calls `$processor` for each item in the iterator while acquiring a lock from `$semaphore` during each operation.
`$processor` receives the current element and an `$emit` callable as arguments.

This function can be used to implement additional concurrent iteration functions and is the base for `map`, `filter`, and `each`. 

Use `LocalSemaphore` if you don't need to synchronize beyond a single process.

```php
function transform(Iterator $iterator, Semaphore $semaphore, callable $processor): Iterator
{
    // ...
}
```