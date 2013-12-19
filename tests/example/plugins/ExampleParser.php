<?php

namespace ExampleParser;

use Cybits\Elbish\Parser\Post;

/**
 * Class ExampleParser
 *
 * @package ExampleParser
 */
class ExampleParser extends Post
{
    /**
     * {@inheritdoc}
     */
    public function getText()
    {
        return strrev($this->text);
    }

    /**
     * {@inheritdoc}
     */
    public function isSupported($fileName)
    {
        $info = pathinfo($fileName);
        $ext = strtolower($info['extension']);

        return in_array($ext, array('example'));
    }
}
