<?php

namespace inisire\fibers\Network;

use inisire\fibers\Network\TCP\Socket;
use inisire\fibers\Network\UDP\DatagramSocket;

class SocketFactory
{
    public function createTCP(): Socket
    {
        return new Socket();
    }

    public function createUDP(): DatagramSocket
    {
        return new DatagramSocket();
    }
}