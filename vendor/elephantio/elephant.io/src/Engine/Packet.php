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
 * Represents packet.
 *
 * @property int $proto Protocol id
 * @property int $type Message type
 * @property string $nsp Namespace
 * @property string $event Event name
 * @property int $ack Acknowledgement id
 * @property array $args Event arguments
 * @property mixed $data Packet data
 * @property int $count Binary attachment count
 * @property \ElephantIO\Engine\Packet[] $next Nested packets
 * @author Toha <tohenk@yahoo.com>
 */
class Packet extends Store
{
    protected function initialize()
    {
        $this->keys = ['+proto', 'type', 'nsp', 'event', 'ack', '!args', '!data', '_next', '_count'];
    }

    /**
     * Set arguments and data from first element of arguments.
     *
     * @param array $args
     * @return \ElephantIO\Engine\Packet
     */
    public function setArgs($args)
    {
        if (is_array($args)) {
            $this->args = $args;
            $this->data = count($args) ? $args[0] : null;
        }

        return $this;
    }

    /**
     * Flatten packet into array of packet.
     *
     * @return \ElephantIO\Engine\Packet[]
     */
    public function flatten()
    {
        $result = [$this];
        foreach ((array) $this->next as $p) {
            $result = array_merge($result, $p->flatten());
        }

        return $result;
    }

    /**
     * Peek packet with matched protocol.
     *
     * @param int $proto
     * @return \ElephantIO\Engine\Packet[]
     */
    public function peek($proto)
    {
        $result = [];
        foreach ($this->flatten() as $p) {
            if ($p->proto === $proto) {
                $result[] = $p;
            }
        }

        return $result;
    }

    /**
     * Peek packet with matched protocol.
     *
     * @param int $proto
     * @return \ElephantIO\Engine\Packet
     */
    public function peekOne($proto)
    {
        return count($packets = $this->peek($proto)) ? $packets[0] : null;
    }

    /**
     * Add nested packet.
     *
     * @param \ElephantIO\Engine\Packet $packet
     * @return \ElephantIO\Engine\Packet
     */
    public function add($packet)
    {
        if (!$this->next) {
            $this->next = [$packet];
        } else {
            $this->next = array_merge($this->next, [$packet]);
        }

        return $this;
    }
}
