<?php

namespace inisire\fibers\Network;

class SocketFactory implements \inisire\fibers\Contract\SocketFactory
{
    public function createTCP(): Socket
    {
        $socket = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);

        return new Socket($socket);
    }

    public function createUDP(): Socket
    {
        $socket = socket_create(AF_INET, SOCK_DGRAM, SOL_UDP);

        return new Socket($socket);
    }
}