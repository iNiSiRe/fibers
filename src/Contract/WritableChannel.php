<?php

namespace inisire\fibers\Contract;

interface WritableChannel
{
    public function write(mixed $data): void;

    public function close();
}