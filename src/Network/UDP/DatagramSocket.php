<?php

namespace inisire\fibers\Network\UDP;

use inisire\fibers\Network\SocketError;

class DatagramSocket
{
    private ?\Socket $socket = null;

    private int $readBufferSize;
    private int $writeBufferSize;

    public function __construct()
    {
        $this->socket = socket_create(AF_INET, SOCK_DGRAM, SOL_UDP);
        socket_set_nonblock($this->socket);

        $this->readBufferSize = socket_get_option($this->socket, SOL_SOCKET, SO_RCVBUF);
        $this->writeBufferSize = socket_get_option($this->socket, SOL_SOCKET, SO_SNDBUF);
    }

    public function __destruct()
    {
        $this->socket = null;
    }

    public function sendTo(string $host, int $port, string $data): false|int
    {
        \Fiber::suspend();

        return socket_sendto($this->socket, $data , strlen($data) , 0 , $host , $port);
    }

    public function read(): string
    {
        $buffer = '';

        do {
            \Fiber::suspend();

            $chunk = socket_read($this->socket, $this->readBufferSize);

            if ($chunk === false && socket_last_error($this->socket) === SOCKET_EAGAIN) {
                \Fiber::suspend();
                continue;
            }

            $buffer .= $chunk;
        } while ($buffer === '');

        return $buffer;
    }

    public function lastError(): SocketError
    {
        $code = socket_last_error($this->socket);

        return new SocketError($code, socket_strerror($code));
    }

    public function clearError(): void
    {
        socket_clear_error($this->socket);
    }
}