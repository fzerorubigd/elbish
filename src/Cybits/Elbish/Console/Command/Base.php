<?php

namespace Cybits\Elbish\Console\Command;


use Symfony\Component\Console\Command\Command;

/**
 * Class Base
 *
 * @package Cybits\Elbish\Console\
 *
 * @method \Cybits\Elbish\Application getApplication() return current application
 */
class Base extends Command
{
    /**
     * Utility function
     *
     * @param string $pattern   pattern to replace
     * @param int    $time      timestamp
     * @param array  $overwrite array of string to overwrite the originals
     *
     * @return string
     */
    protected function getPattern($pattern, $time, array $overwrite = array())
    {
        $date = date('y-m-d-H-i-s', $time);
        $date = explode('-', $date);
        $source = array(
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
     * Create a link to index file (or simply create a copy here?)
     *
     * @param string $file  the base file name
     * @param string $index the target file
     */
    protected function makeIndexLink($file, $index)
    {
        //This is here for forward-compatibility
        //For now, just copy the content here again.
        copy($file, $index);
    }
}
