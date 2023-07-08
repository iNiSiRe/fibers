<?php

namespace inisire\fibers\Contract;


interface Socket
{
    public function connect(string $host, int $port, ?int $timeout = null): bool;

    public function isConnected(): bool;

    public function write(string $data): false|int;

    public function sendTo(string $address, ?int $port, string $data): false|int;

    public function read(): string;

    public function close(): void;

    public function lastError(): SocketError;

    public function clearError(): void;
}