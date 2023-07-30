<?php

namespace inisire\fibers\Network\Exception;


class ConnectionException extends \Exception
{
    public function __construct(int $code, string $message, ?\Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}