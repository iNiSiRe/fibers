<?php

namespace inisire\fibers;

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
    public function await(): mixed
    {
        while ($this->value === null) {
            \Fiber::suspend();
        }

        return $this->value;
    }
}