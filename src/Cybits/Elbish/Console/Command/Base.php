<?php

namespace Cybits\Elbish\Console\Command;


use Cybits\Elbish\Application;
use Symfony\Component\Console\Command\Command;

/**
 * Class Base
 *
 * @package Cybits\Elbish\Console\Command
 */
class Base extends Command
{
    protected function getPattern($pattern,$time = null, array $overwrite = array())
    {
        if (!$time) {
            $time = time();
        }
        $date = date('y-m-d-H-i-s', $time);
        $date = explode('-', $date);
        $source = array (
            ':year' => $date[0],
            ':month' => $date[1],
            ':day' => $date[2],
            ':hour' => $date[3],
            ':minute' => $date[4],
            ':second' => $date[5],
        );
        $source = array_merge($source, $overwrite);

        return strtr($pattern, $source);
    }

    /**
     * @return Application
     */
    public function getApplication()
    {
        return parent::getApplication();
    }
} 