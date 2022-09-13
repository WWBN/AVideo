---
title: Semaphore
permalink: /semaphore
---
[Semaphores](https://en.wikipedia.org/wiki/Semaphore_%28programming%29) are another synchronization primitive in addition to [mutual exclusion](./mutex.md).

Instead of providing exclusive access to a single party, they provide access to a limited set of N parties at the same time.
This makes them great to control concurrency, e.g. limiting an HTTP client to X concurrent requests, so the HTTP server doesn't get overwhelmed.

Similar to [`Mutex`](./mutex.md), `Lock` instances can be acquired using `Semaphore::acquire()`.
Please refer to the `Mutex` documentation for additional usage documentation, as they're basically equivalent except for the fact that `Mutex` is always a `Semaphore` with a count of exactly one party.

In many cases you can use [concurrent iterators](./concurrent-iterator.md) instead of directly using a `Semaphore`.