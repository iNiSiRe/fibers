<?php

namespace inisire\fibers;

use inisire\fibers\Promise\Timeout;

/**
 * @template T
 */
class Promise {
    /**
     * @var T|null
     */
    private mixed $value = null;

    /**
     * @param T $value
     *
     * @return void
     */
    public function resolve(mixed $value): void
    {
        $this->value = $value;
    }

    /**
     * @return T
     *
     * @throws \Throwable
     */
    public function await(?Timeout $timeout = null): mixed
    {
        if ($timeout) {
            Scheduler::async(function () use ($timeout) {
                Scheduler::sleep($timeout->getSeconds());
                $this->resolve($timeout->getRejectValue());
            });
        }

        while ($this->value === null) {
            \Fiber::suspend();
        }

        return $this->value;
    }
}