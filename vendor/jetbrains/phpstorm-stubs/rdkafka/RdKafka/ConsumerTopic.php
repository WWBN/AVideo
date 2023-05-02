<?php

namespace RdKafka;

class ConsumerTopic extends Topic
{
    private function __construct()
    {
    }

    /**
     * @param int $partition
     * @param int $timeout_ms
     *
     * @return Message
     */
    public function consume($partition, $timeout_ms)
    {
    }

    /**
     * @param int   $partition
     * @param int   $offset
     * @param Queue $queue
     *
     * @return void
     */
    public function consumeQueueStart($partition, $offset, Queue $queue)
    {
    }

    /**
     * @param int $partition
     * @param int $offset
     *
     * @return void
     */
    public function consumeStart($partition, $offset)
    {
    }

    /**
     * @param int $partition
     *
     * @return void
     */
    public function consumeStop($partition)
    {
    }

    /**
     * @param int $partition
     * @param int $offset
     *
     * @return void
     */
    public function offsetStore($partition, $offset)
    {
    }
}
