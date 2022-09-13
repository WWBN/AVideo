<?php

namespace Amp\Parallel\Sync;

use Amp\Promise;

/**
 * Interface for sending messages between execution contexts.
 */
interface Channel
{
    /**
     * @return \Amp\Promise<mixed>
     *
     * @throws \Amp\Parallel\Context\StatusError Thrown if the context has not been started.
     * @throws \Amp\Parallel\Sync\SynchronizationError If the context has not been started or the context
     *     unexpectedly ends.
     * @throws \Amp\Parallel\Sync\ChannelException If receiving from the channel fails.
     * @throws \Amp\Parallel\Sync\SerializationException If unserializing the data fails.
     */
    public function receive(): Promise;

    /**
     * @param mixed $data
     *
     * @return \Amp\Promise<int> Resolves with the number of bytes sent on the channel.
     *
     * @throws \Amp\Parallel\Context\StatusError Thrown if the context has not been started.
     * @throws \Amp\Parallel\Sync\SynchronizationError If the context has not been started or the context
     *     unexpectedly ends.
     * @throws \Amp\Parallel\Sync\ChannelException If sending on the channel fails.
     * @throws \Error If an ExitResult object is given.
     * @throws \Amp\Parallel\Sync\SerializationException If serializing the data fails.
     */
    public function send($data): Promise;
}
