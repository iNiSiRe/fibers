<?php

namespace inisire\fibers;


class Broadcast implements Contract\WritableChannel
{
    /**
     * @var \WeakMap<Channel,bool>
     */
    private \WeakMap $channels;

    public function __construct()
    {
        $this->channels = new \WeakMap();
    }

    public function attach(Contract\ReadableChannel $channel): Contract\ReadableChannel
    {
        $this->channels[$channel] = true;

        return $channel;
    }

    public function write(mixed $data): void
    {
        foreach ($this->channels as $channel => $_) {
            $channel->write($data);
        }
    }

    public function close()
    {
        foreach ($this->channels as $channel => $_) {
            $channel->close();
        }
    }
}