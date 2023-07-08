<?php

namespace inisire\fibers\Contract;

interface SocketFactory
{
    public function createTCP(): Socket;

    public function createUDP(): Socket;
}