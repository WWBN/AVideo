<?php

namespace React\EventLoop\Timer;

use React\EventLoop\LoopInterface;

interface TimerInterface
{
    /**
     * Get the loop with which this timer is associated
     *
     * @return LoopInterface
     */
    public function getLoop();

    /**
     * Get the interval after which this timer will execute, in seconds
     *
     * @return float
     */
    public function getInterval();

    /**
     * Get the callback that will be executed when this timer elapses
     *
     * @return callable
     */
    public function getCallback();

    /**
     * Set arbitrary data associated with timer
     *
     * @param mixed $data
     */
    public function setData($data);

    /**
     * Get arbitrary data associated with timer
     *
     * @return mixed
     */
    public function getData();

    /**
     * Determine whether the time is periodic
     *
     * @return bool
     */
    public function isPeriodic();

    /**
     * Determine whether the time is active
     *
     * @return bool
     */
    public function isActive();

    /**
     * Cancel this timer
     */
    public function cancel();
}
