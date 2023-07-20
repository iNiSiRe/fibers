<?php

namespace inisire\fibers;

class Scheduler {
    private static ?self $instance = null;

    /**
     * @var iterable<\Fiber>
     */
    private array $fibers = [];

    private function __construct()
    {
        register_shutdown_function(function () {
            $this->run();
        });
    }

    public function run(): void
    {
        while (count($this->fibers)) {
            foreach ($this->fibers as $i => $fiber) {
                if (!$fiber->isStarted()) {
                    $fiber->start();
                } elseif ($fiber->isSuspended()) {
                    $fiber->resume();
                }

                if ($fiber->isTerminated()) {
                    unset($this->fibers[$i]);
                }
            }
        }
    }

    public static function instance(): static
    {
        if (!self::$instance) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    public function schedule(\Fiber $fiber): void
    {
        $this->fibers[] = $fiber;
    }

    public static function sleep(float $seconds): void
    {
        $start = microtime(true);

        while ((microtime(true) - $start) < $seconds) {
            \Fiber::suspend();
            usleep(5000);
        }
    }
}