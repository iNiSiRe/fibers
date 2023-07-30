<?php

namespace inisire\fibers\Network\TCP;

use Evenement\EventEmitterTrait;
use inisire\fibers\Network\Exception\ConnectionException;
use inisire\fibers\Network\Exception\Timeout;
use function inisire\fibers\asleep;
use function inisire\fibers\async;

class Connector
{
    /**
     * @throws ConnectionException
     */
    public function connect(string $host, int $port, ?int $timeout = null): Socket
    {
        $start = microtime(true);

        \Fiber::suspend();

        $handle = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);
        socket_set_nonblock($handle);

        do {
            \Fiber::suspend();

            try {
                $connected = socket_connect($handle, $host, $port);
                $error = socket_last_error($handle);
                socket_clear_error($handle);
            } catch (\Error $error) {
                throw new ConnectionException($error->getCode(), $error->getMessage(), $error);
            }

            if (!$connected) {
                if ($timeout !== null && microtime(true) - $start > $timeout) {
                    throw new ConnectionException(-1, 'Timeout');
                }

                if ($error === 0 || $error === SOCKET_EALREADY || $error === SOCKET_EINPROGRESS) {
                    asleep(1);
                    continue;
                }

                if ($error === SOCKET_EISCONN) {
                    $connected = true;
                    break;
                }
            }
        } while (!$connected);

        return new Socket($handle);
    }
}