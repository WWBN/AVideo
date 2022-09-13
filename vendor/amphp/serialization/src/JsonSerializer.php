<?php

namespace Amp\Serialization;

final class JsonSerializer implements Serializer
{
    // Equal to JSON_THROW_ON_ERROR, only available in PHP 7.3+.
    private const THROW_ON_ERROR = 4194304;

    /** @var bool */
    private $associative;

    /** @var int */
    private $encodeOptions;

    /** @var int */
    private $decodeOptions;

    /** @var int */
    private $depth;

    /**
     * Creates a JSON serializer that decodes objects to associative arrays.
     *
     * @param int $encodeOptions @see \json_encode() options parameter.
     * @param int $decodeOptions @see \json_decode() options parameter.
     * @param int $depth Maximum recursion depth.
     *
     * @return self
     */
    public static function withAssociativeArrays(int $encodeOptions = 0, int $decodeOptions = 0, int $depth = 512): self
    {
        return new self(true, $encodeOptions, $decodeOptions, $depth);
    }

    /**
     * Creates a JSON serializer that decodes objects to instances of stdClass.
     *
     * @param int $encodeOptions @see \json_encode() options parameter.
     * @param int $decodeOptions @see \json_decode() options parameter.
     * @param int $depth Maximum recursion depth.
     *
     * @return self
     */
    public static function withObjects(int $encodeOptions = 0, int $decodeOptions = 0, int $depth = 512): self
    {
        return new self(false, $encodeOptions, $decodeOptions, $depth);
    }

    private function __construct(bool $associative, int $encodeOptions = 0, int $decodeOptions = 0, int $depth = 512)
    {
        $this->associative = $associative;
        $this->depth = $depth;

        // We don't want to throw on errors, we manually check for errors using json_last_error().
        $this->encodeOptions = $encodeOptions & ~self::THROW_ON_ERROR;
        $this->decodeOptions = $decodeOptions & ~self::THROW_ON_ERROR;
    }

    public function serialize($data): string
    {
        $result = \json_encode($data, $this->encodeOptions, $this->depth);

        switch ($code = \json_last_error()) {
            case \JSON_ERROR_NONE:
                return $result;

            default:
                throw new SerializationException(\json_last_error_msg(), $code);
        }
    }

    public function unserialize(string $data)
    {
        $result = \json_decode($data, $this->associative, $this->depth, $this->decodeOptions);

        switch ($code = \json_last_error()) {
            case \JSON_ERROR_NONE:
                return $result;

            default:
                throw new SerializationException(\json_last_error_msg(), $code);
        }
    }
}
