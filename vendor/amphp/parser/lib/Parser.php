<?php

namespace Amp\Parser;

class Parser {
    /** @var \Generator */
    private $generator;

    /** @var string */
    private $buffer = '';

    /** @var int|string|null */
    private $delimiter;

    /**
     * @param \Generator $generator
     *
     * @throws InvalidDelimiterError If the generator yields an invalid delimiter.
     * @throws \Throwable If the generator throws.
     */
    public function __construct(\Generator $generator) {
        $this->generator = $generator;

        $this->delimiter = $this->generator->current();

        if (!$this->generator->valid()) {
            $this->generator = null;
            return;
        }

        if ($this->delimiter !== null
            && (!\is_int($this->delimiter) || $this->delimiter <= 0)
            && (!\is_string($this->delimiter) || !\strlen($this->delimiter))
        ) {
            throw new InvalidDelimiterError(
                $generator,
                \sprintf(
                    "Invalid value yielded: Expected NULL, an int greater than 0, or a non-empty string; %s given",
                    \is_object($this->delimiter) ? \sprintf("instance of %s", \get_class($this->delimiter)) : \gettype($this->delimiter)
                )
            );
        }
    }

    /**
     * Cancels the generator parser and returns any remaining data in the internal buffer. Writing data after calling
     * this method will result in an error.
     *
     * @return string
     */
    final public function cancel(): string {
        $this->generator = null;
        return $this->buffer;
    }

    /**
     * @return bool True if the parser can still receive more data to parse, false if it has ended and calling push
     *     will throw an exception.
     */
    final public function isValid(): bool {
        return $this->generator !== null;
    }

    /**
     * Adds data to the internal buffer and tries to continue parsing.
     *
     * @param string $data Data to append to the internal buffer.
     *
     * @throws InvalidDelimiterError If the generator yields an invalid delimiter.
     * @throws \Error If parsing has already been cancelled.
     * @throws \Throwable If the generator throws.
     */
    final public function push(string $data) {
        if ($this->generator === null) {
            throw new \Error("The parser is no longer writable");
        }

        $this->buffer .= $data;
        $end = false;

        try {
            while ($this->buffer !== "") {
                if (\is_int($this->delimiter)) {
                    if (\strlen($this->buffer) < $this->delimiter) {
                        break; // Too few bytes in buffer.
                    }

                    $send = \substr($this->buffer, 0, $this->delimiter);
                    $this->buffer = \substr($this->buffer, $this->delimiter);
                } elseif (\is_string($this->delimiter)) {
                    if (($position = \strpos($this->buffer, $this->delimiter)) === false) {
                        break;
                    }

                    $send = \substr($this->buffer, 0, $position);
                    $this->buffer = \substr($this->buffer, $position + \strlen($this->delimiter));
                } else {
                    $send = $this->buffer;
                    $this->buffer = "";
                }

                $this->delimiter = $this->generator->send($send);

                if (!$this->generator->valid()) {
                    $end = true;
                    break;
                }

                if ($this->delimiter !== null
                    && (!\is_int($this->delimiter) || $this->delimiter <= 0)
                    && (!\is_string($this->delimiter) || !\strlen($this->delimiter))
                ) {
                    throw new InvalidDelimiterError(
                        $this->generator,
                        \sprintf(
                            "Invalid value yielded: Expected NULL, an int greater than 0, or a non-empty string; %s given",
                            \is_object($this->delimiter) ? \sprintf("instance of %s", \get_class($this->delimiter)) : \gettype($this->delimiter)
                        )
                    );
                }
            }
        } catch (\Throwable $exception) {
            $end = true;
            throw $exception;
        } finally {
            if ($end) {
                $this->generator = null;
            }
        }
    }
}
