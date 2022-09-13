<?php

namespace Amp\Sync\Internal;

use Amp\Delayed;
use Amp\Promise;
use Amp\Sync\Lock;
use function Amp\call;

/** @internal */
final class MutexStorage extends \Threaded
{
    public const LATENCY_TIMEOUT =  10;

    /** @var bool */
    private $locked = false;

    public function acquire(): Promise
    {
        return call(function (): \Generator {
            $tsl = function (): bool {
                if ($this->locked) {
                    return true;
                }

                $this->locked = true;
                return false;
            };

            while ($this->locked || $this->synchronized($tsl)) {
                yield new Delayed(self::LATENCY_TIMEOUT);
            }

            return new Lock(0, function (): void {
                $this->locked = false;
            });
        });
    }
}
