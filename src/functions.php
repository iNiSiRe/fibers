<?php

namespace inisire\fibers;

function async(callable $function): void
{
    $scheduler = Scheduler::instance();
    $scheduler->schedule(new \Fiber($function));
}

function sleep(float $seconds): void
{
    $start = microtime(true);

    while ((microtime(true) - $start) < $seconds) {
        \Fiber::suspend();
        usleep(5000);
    }
}