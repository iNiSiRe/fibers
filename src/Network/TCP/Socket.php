<?php

namespace inisire\fibers\Network\TCP;

use Evenement\EventEmitterTrait;
use inisire\fibers\Network\Exception\ConnectionException;
use function inisire\fibers\async;

class Socket
{
    use EventEmitterTrait;

    private bool $open;

    private int $readBufferSize;
    private int $writeBufferSize;

    private bool $reader = false;

    public function __construct(
        private readonly \Socket $handle,
    )
    {
        echo 'Socket #' . spl_object_hash($this) . ' created' . PHP_EOL;

        $this->open = true;
        $this->readBufferSize = socket_get_option($this->handle, SOL_SOCKET, SO_RCVBUF);
        $this->writeBufferSize = socket_get_option($this->handle, SOL_SOCKET, SO_SNDBUF);
    }

    /**
     * @throws ConnectionException
     */
    public function write(string $data): false|int
    {
        \Fiber::suspend();

        $sent = 0;
        $size = strlen($data);

        while ($this->isOpen() && $sent < $size) {
            $chunkLength = strlen($data);

            try {
                $chunkSent = socket_write($this->handle, $data, $chunkLength);
//            $chunkSent = socket_send($this->socket, $data, $chunkLength, 0);
                $error = socket_last_error($this->handle);
                socket_clear_error($this->handle);
            } catch (\Error $error) {
                $this->close();
                throw new ConnectionException($error->getCode(), $error->getMessage(), $error);
            }

//            echo 'WRITE: ' .  $chunkSent . ' / ' . $error . ' / ' . socket_strerror($error) . PHP_EOL;

            if ($chunkSent === false || $chunkSent === 0) {
                if ($error === SOCKET_EAGAIN) {
                    \Fiber::suspend();
                    continue;
                } else {
                    $this->close();
                    throw new ConnectionException($error, socket_strerror($error));
                }
            }

            if ($chunkSent < $chunkLength) {
                $data = substr($data, $chunkSent);
            }

            $sent += $chunkSent;
        }

        return $sent;
    }

    /**
     * @throws ConnectionException
     */
    private function startReader(): void
    {
        $this->reader = true;

        \Fiber::suspend();

        while ($this->isOpen()) {
            try {
                $received = socket_recv($this->handle, $chunk, $this->readBufferSize, 0);
                $error = socket_last_error($this->handle);
                socket_clear_error($this->handle);
            } catch (\Error $error) {
                $this->close();
                throw new ConnectionException($error->getCode(), $error->getMessage(), $error);
            }

//            echo 'READ: ' . (($received === false) ? 'false' : $received) . ' / ' . $error . ' / ' . socket_strerror($error) . PHP_EOL;

            if ($received === false || $chunk === null || $chunk === '') {
                if ($error === 0 || $error === SOCKET_EAGAIN) {
                    \Fiber::suspend();
                    continue;
                } else {
                    $this->close();
                    throw new ConnectionException($error, socket_strerror($error));
                }
            }

            $this->emit('data', [$chunk]);
        }
    }

    public function onData(callable $handler): void
    {
        $this->on('data', $handler);

        if (!$this->reader) {
            async(function () {
                try {
                    $this->startReader();
                } catch (ConnectionException $exception) {
                }
            });
        }
    }

    public function onClose(callable $handler): void
    {
        $this->on('close', $handler);
    }

    public function close(): void
    {
        if (!$this->isOpen()) {
            return;
        }

        $this->open = false;
        socket_shutdown($this->handle);
        socket_close($this->handle);

        $this->emit('close');
        $this->removeAllListeners();
    }

    public function __destruct()
    {
        echo 'Socket #' . spl_object_hash($this) . ' destroyed' . PHP_EOL;
    }

    public function isOpen(): bool
    {
        return $this->open;
    }
}