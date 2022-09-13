<?php

namespace Amp\Serialization;

interface Serializer
{
    /**
     * @param mixed $data
     *
     * @return string
     *
     * @throws SerializationException
     */
    public function serialize($data): string;

    /**
     * @param string $data
     *
     * @return mixed The unserialized data.
     *
     * @throws SerializationException
     */
    public function unserialize(string $data);
}
