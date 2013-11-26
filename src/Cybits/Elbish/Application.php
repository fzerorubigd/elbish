<?php

namespace Cybits\Elbish;

use Cybits\Elbish\Console\Command\Test;

class Application extends \Symfony\Component\Console\Application
{

    const VERSION = '0.1.0';
    const APP_NAME = 'elbish, Static publishing system';

    public function __construct()
    {
        parent::__construct(self::APP_NAME, self::VERSION);
        $this->add(new Test());
    }
}
