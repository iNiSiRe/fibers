<?php

namespace inisire\fibers\Network;

class Socket
{
    private int $readBufferSize;
    private int $writeBufferSize;

    private bool $connected = false;

    public function __construct(
        private \Socket $socket
    )
    {
        socket_set_nonblock($this->socket);
        $this->readBufferSize = socket_get_option($this->socket, SOL_SOCKET, SO_RCVBUF);
        $this->writeBufferSize = socket_get_option($this->socket, SOL_SOCKET, SO_SNDBUF);
    }

    public static function tcp(): static
    {
        $socket = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);

        return new static($socket);
    }

    public static function udp(): static
    {
        $socket = socket_create(AF_INET, SOCK_DGRAM, SOL_UDP);

        return new static($socket);
    }

    public function connect(string $host, int $port, ?int $timeout = null): bool
    {
        \Fiber::suspend();

        $start = microtime(true);

        while (!@socket_connect($this->socket, $host, $port)) {
            $error = socket_last_error($this->socket);

            if ($error !== SOCKET_EALREADY && $error !== SOCKET_EINPROGRESS) {
                $this->close();
                return false;
            }

            if ($timeout !== null && microtime(true) - $start > $timeout) {
                $this->close();
                return false;
            }

            \Fiber::suspend();
        }

        $this->connected = true;

        return true;
    }

    public function write(string $data): false|int
    {
        \Fiber::suspend();

        return socket_write($this->socket, $data, strlen($data));
    }

    public function sendTo(string $address, ?int $port, string $data): false|int
    {
        \Fiber::suspend();

        return socket_sendto($this->socket, $data , strlen($data) , 0 , $address , $port);
    }

    public function read(): string
    {
        $buffer = '';

        do {
            \Fiber::suspend();

            $chunk = socket_read($this->socket, $this->readBufferSize);

            if ($chunk === false && socket_last_error($this->socket) === SOCKET_EAGAIN) {
                \Fiber::suspend();
            }

            $buffer .= $chunk;
        } while ($chunk !== false && strcmp($chunk, '') !== 0);

        return $buffer;
    }

    public function close(): void
    {
        $this->connected = false;
        socket_close($this->socket);
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

    public function __destruct()
    {
        $this->close();
    }

    public function isConnected(): bool
    {
        return $this->connected;
    }
}