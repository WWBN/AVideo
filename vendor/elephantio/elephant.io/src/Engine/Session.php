<?php

/**
 * This file is part of the Elephant.io package
 *
 * For the full copyright and license information, please view the LICENSE file
 * that was distributed with this source code.
 *
 * @copyright Wisembly
 * @license   http://www.opensource.org/licenses/MIT-License MIT License
 */

namespace ElephantIO\Engine;

/**
 * Represents session.
 *
 * @property string $id Session id
 * @property float $heartbeat Last heartbeat time
 * @property float[] $timeouts Ping timeout and interval
 * @property string[] $upgrades Upgradable transports
 * @property int $max_payload Maximum payload length
 * @author Baptiste Clavié <baptiste@wisembly.com>
 * @author Toha <tohenk@yahoo.com>
 */
class Session extends Store
{
    protected function initialize()
    {
        $this->keys = ['id', 'upgrades', 'timeouts', 'max_payload', '_heartbeat'];
    }

    protected function getTime()
    {
        return \microtime(true);
    }

    /**
     * Get ping timeout.
     *
     * @return float
     */
    public function getTimeout()
    {
        return $this->timeouts['timeout'];
    }

    /**
     * Get ping interval.
     *
     * @return float
     */
    public function getInterval()
    {
        return $this->timeouts['interval'];
    }

    /**
     * Checks whether a new heartbeat is necessary, and does a new heartbeat if it is the case.
     *
     * @return bool true if there was a heartbeat, false otherwise
     */
    public function needsHeartbeat()
    {
        if ($this->timeouts['interval'] > 0) {
            $time = $this->getTime();
            $heartbeat = $this->timeouts['interval'] + $this->heartbeat - 5;
            if ($time > $heartbeat) {
                return true;
            }
        }

        return false;
    }

    /**
     * Reset heart beat.
     *
     * @return \ElephantIO\Engine\Session
     */
    public function resetHeartbeat()
    {
        $this->heartbeat = $this->getTime();

        return $this;
    }

    /**
     * Create session from array.
     *
     * @param array $array
     * @return \ElephantIO\Engine\Session
     */
    public static function from($array)
    {
        $mapped = [];
        foreach ($array as $k => $v) {
            $key = $k;
            switch ($k) {
                case 'sid':
                    $key = 'id';
                    break;
                case 'pingInterval':
                    $key = 'timeouts';
                    $v = ['interval' => $v];
                    break;
                case 'pingTimeout':
                    $key = 'timeouts';
                    $v = ['timeout' => $v];
                    break;
                case 'maxPayload':
                    $key = 'max_payload';
                    break;
            }
            if (is_array($v) && isset($mapped[$key])) {
                $mapped[$key] = array_merge($mapped[$key], $v);
            } else {
                $mapped[$key] = $v;
            }
        }

        return static::create($mapped);
    }
}
