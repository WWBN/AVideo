<?php

namespace Amp\Process;

use Amp\ByteStream\InputStream;
use Amp\ByteStream\PendingReadError;
use Amp\ByteStream\ResourceInputStream;
use Amp\ByteStream\StreamException;
use Amp\Deferred;
use Amp\Failure;
use Amp\Promise;
use Amp\Success;

final class ProcessInputStream implements InputStream
{
    /** @var Deferred */
    private $initialRead;

    /** @var bool */
    private $shouldClose = false;

    /** @var bool */
    private $referenced = true;

    /** @var ResourceInputStream */
    private $resourceStream;

    /** @var StreamException|null */
    private $error;

    public function __construct(Promise $resourceStreamPromise)
    {
        $resourceStreamPromise->onResolve(function ($error, $resourceStream) {
            if ($error) {
                $this->error = new StreamException("Failed to launch process", 0, $error);
                if ($this->initialRead) {
                    $initialRead = $this->initialRead;
                    $this->initialRead = null;
                    $initialRead->fail($this->error);
                }
                return;
            }

            $this->resourceStream = $resourceStream;

            if (!$this->referenced) {
                $this->resourceStream->unreference();
            }

            if ($this->shouldClose) {
                $this->resourceStream->close();
            }

            if ($this->initialRead) {
                $initialRead = $this->initialRead;
                $this->initialRead = null;
                $initialRead->resolve($this->shouldClose ? null : $this->resourceStream->read());
            }
        });
    }

    /**
     * Reads data from the stream.
     *
     * @return Promise Resolves with a string when new data is available or `null` if the stream has closed.
     *
     * @throws PendingReadError Thrown if another read operation is still pending.
     */
    public function read(): Promise
    {
        if ($this->initialRead) {
            throw new PendingReadError;
        }

        if ($this->error) {
            return new Failure($this->error);
        }

        if ($this->resourceStream) {
            return $this->resourceStream->read();
        }

        if ($this->shouldClose) {
            return new Success; // Resolve reads on closed streams with null.
        }

        $this->initialRead = new Deferred;

        return $this->initialRead->promise();
    }

    public function reference()
    {
        $this->referenced = true;

        if ($this->resourceStream) {
            $this->resourceStream->reference();
        }
    }

    public function unreference()
    {
        $this->referenced = false;

        if ($this->resourceStream) {
            $this->resourceStream->unreference();
        }
    }

    public function close()
    {
        $this->shouldClose = true;

        if ($this->initialRead) {
            $initialRead = $this->initialRead;
            $this->initialRead = null;
            $initialRead->resolve();
        }

        if ($this->resourceStream) {
            $this->resourceStream->close();
        }
    }
}
