<?php

namespace inisire\fibers\Contract;

interface ReadableChannel
{
    public function read(): mixed;

    public function iterate(): iterable;
}