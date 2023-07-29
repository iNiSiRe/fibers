<?php

namespace inisire\fibers\Network\TCP;

use Evenement\EventEmitterTrait;
use inisire\fibers\Network\SocketError;

class Socket
{
    use EventEmitterTrait;

    private \Socket $socket;

    private bool $connected = false;

    private int $readBufferSize;
    private int $writeBufferSize;

    public function __construct()
    {
        $this->socket = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);
        socket_set_nonblock($this->socket);

        $this->readBufferSize = socket_get_option($this->socket, SOL_SOCKET, SO_RCVBUF);
        $this->writeBufferSize = socket_get_option($this->socket, SOL_SOCKET, SO_SNDBUF);
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
        if (!$this->isConnected()) {
            throw new \RuntimeException('Connection is closed');
        }

        \Fiber::suspend();

        $sent = 0;
        $size = strlen($data);

        while ($sent < $size) {
            $chunkLength = strlen($data);
            $chunkSent = socket_write($this->socket, $data, $chunkLength);

            if ($chunkSent === false || $chunkSent === 0) {
                $error = $this->lastError();
                if ($error->getCode() === SOCKET_EAGAIN) {
                    \Fiber::suspend();
                    continue;
                } else {
                    $this->emit('error', [$this->lastError()]);
                    $this->close();
                    break;
                }
            }

            if ($chunkSent < $chunkLength) {
                $data = substr($data, $chunkSent);
            }

            $sent += $chunkSent;
        }

        return $sent;
    }

    public function read(): string
    {
        if (!$this->isConnected()) {
            throw new \RuntimeException('Connection is closed');
        }

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

    public function close(): void
    {
        if (!$this->isConnected()) {
            throw new \RuntimeException('Connection is closed');
        }

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

    public function onError(callable $handler)
    {
        $this->on('error', $handler);
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