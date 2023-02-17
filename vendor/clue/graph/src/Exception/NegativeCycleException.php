<?php

namespace Fhaculty\Graph\Exception;

use Fhaculty\Graph\Walk;
use Fhaculty\Graph;

class NegativeCycleException extends UnexpectedValueException implements Graph\Exception
{
    /**
     * instance of the cycle
     *
     * @var Walk
     */
    private $cycle;

    public function __construct($message, $code = NULL, $previous = NULL, Walk $cycle = null)
    {
        // $cycle is required, but required argument may not appear after option arguments as of PHP 8
        if ($cycle === null) {
            throw new \InvalidArgumentException('Missing required cycle');
        }

        parent::__construct($message, $code, $previous);
        $this->cycle = $cycle;
    }

    /**
     *
     * @return Walk
     */
    public function getCycle()
    {
        return $this->cycle;
    }
}
