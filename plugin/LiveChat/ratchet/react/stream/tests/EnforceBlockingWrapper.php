<?php

namespace React\Tests\Stream;

/**
 * Used to test dummy stream resources that do not support setting non-blocking mode
 *
 * @link http://php.net/manual/de/class.streamwrapper.php
 */
class EnforceBlockingWrapper
{
    public function stream_open($path, $mode, $options, &$opened_path)
    {
        return true;
    }

    public function stream_cast($cast_as)
    {
        return false;
    }

    public function stream_eof()
    {
        return false;
    }

    public function stream_set_option($option, $arg1, $arg2)
    {
        if ($option === STREAM_OPTION_BLOCKING) {
            return false;
        }

        return true;
    }
}
