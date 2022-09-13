---
title: Mutex
permalink: /mutex
---
[Mutual exclusion](https://en.wikipedia.org/wiki/Mutual_exclusion) can be achieved using `Amp\Sync\synchronized()` and any `Mutex` implementation, or by manually using the `Mutex` instance to acquire a lock. 

Locks are acquired using `Mutex::acquire()`, which returns a `Promise` that resolves to an instance of `Lock` once the lock has been successfully acquired.

As long as the resulting `Lock` object isn't released using `Lock::release()` or by being garbage collected, the holder of the lock can exclusively run some code as long as all other parties running the same code also acquire a lock before doing so.

### Examples

```php
function writeExclusively(Amp\Sync\Mutex $mutex, string $filePath, string $data) {
    return Amp\call(function () use ($mutex, $filePath, $data) {
        /** @var Amp\Sync\Lock $lock */
        $lock = yield $mutex->acquire();
        
        $this->fileHandle = yield Amp\File\open($filePath, 'w');
        yield $this->fileHandle->write($data);
        
        $lock->release();
    });
}
```

```php
function writeExclusively(Amp\Sync\Mutex $mutex, string $filePath, string $data) {
    return Amp\Sync\synchronized($mutex, function () use ($filePath, $data) {
        $this->fileHandle = yield Amp\File\open($filePath, 'w');
        yield $this->fileHandle->write($data);
    });
}
```
