<?php

namespace inisire\fibers;


/**
 * @template T
 */
class Channel implements Contract\ReadableChannel, Contract\WritableChannel
{
    private ?\SplQueue $queue = null;

    private bool $closed = false;

    public function __construct()
    {
        $this->queue = new \SplQueue();
    }

    public function __destruct()
    {
        $this->queue = null;
        $this->close();
    }

    /**
     * @param T $data
     */
    public function write(mixed $data): void
    {
        $this->queue->push($data);
    }

    /**
     * @return T
     */
    public function read(): mixed
    {
        while ($this->queue->isEmpty()) {
            \Fiber::suspend();
        }

        return $this->queue->shift();
    }

    public function close()
    {
        $this->closed = true;
    }

    public function iterate(): iterable
    {
        while (!$this->closed) {
            yield $this->read();
        }
    }
}