<?php

namespace Google\Auth\Tests;

use GuzzleHttp\ClientInterface;
use PHPUnit\Framework\TestCase;

abstract class BaseTest extends TestCase
{
    protected function onlyGuzzle5()
    {
        if ($this->getGuzzleMajorVersion() !== 5) {
            $this->markTestSkipped('Guzzle 5 only');
        }
    }

    protected function onlyGuzzle6()
    {
        if ($this->getGuzzleMajorVersion() !== 6) {
            $this->markTestSkipped('Guzzle 6 only');
        }
    }

    protected function onlyGuzzle6And7()
    {
        if (!in_array($this->getGuzzleMajorVersion(), [6, 7])) {
            $this->markTestSkipped('Guzzle 6 and 7 only');
        }
    }

    protected function onlyGuzzle7()
    {
        if ($this->getGuzzleMajorVersion() !== 7) {
            $this->markTestSkipped('Guzzle 7 only');
        }
    }

    protected function getGuzzleMajorVersion()
    {
        if (defined('GuzzleHttp\ClientInterface::MAJOR_VERSION')) {
            return ClientInterface::MAJOR_VERSION;
        }

        if (defined('GuzzleHttp\ClientInterface::VERSION')) {
            return (int) substr(ClientInterface::VERSION, 0, 1);
        }

        $this->fail('Unable to determine the currently used Guzzle Version');
    }

    /**
     * @see Google\Auth\$this->getValidKeyName
     */
    public function getValidKeyName($key)
    {
        return preg_replace('|[^a-zA-Z0-9_\.! ]|', '', $key);
    }
}
