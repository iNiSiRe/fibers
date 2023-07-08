<?php

namespace inisire\fibers\Contract;

interface SocketError
{
    public function getCode(): int;

    public function getMessage(): string;
}