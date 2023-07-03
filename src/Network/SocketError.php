<?php

namespace inisire\fibers\Network;

class SocketError
{
    public function __construct(
        private readonly int    $code,
        private readonly string $message
    )
    {
    }

    public function getCode(): int
    {
        return $this->code;
    }

    public function getMessage(): string
    {
        return $this->message;
    }
}