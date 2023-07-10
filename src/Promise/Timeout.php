<?php

namespace inisire\fibers\Promise;

class Timeout
{
    public function __construct(
        private readonly float $seconds,
        private readonly mixed $rejectValue
    )
    {
    }

    public function getSeconds(): float
    {
        return $this->seconds;
    }

    public function getRejectValue(): mixed
    {
        return $this->rejectValue;
    }
}