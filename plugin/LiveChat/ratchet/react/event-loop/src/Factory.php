<?php

namespace React\EventLoop;

class Factory
{
    public static function create()
    {
        // @codeCoverageIgnoreStart
        if (function_exists('event_base_new')) {
            return new LibEventLoop();
        } elseif (class_exists('libev\EventLoop', false)) {
            return new LibEvLoop;
        } elseif (class_exists('EventBase', false)) {
            return new ExtEventLoop;
        }

        return new StreamSelectLoop();
        // @codeCoverageIgnoreEnd
    }
}
