<?php

namespace inisire\fibers\Network\Exception;


class Timeout extends ConnectionException
{
    public function __construct()
    {
        parent::__construct(-1, 'Timeout');
    }
}