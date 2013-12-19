<?php

namespace Cybits\Elbish\Parser\Post;

use Cybits\Elbish\Parser\Post;

/**
 * Class Markdown
 *
 * @package Cybits\Elbish\Parser\Post
 */
class Markdown extends Post
{
    /**
     * {@inheritdoc}
     */
    public function getText()
    {
        return \Michelf\Markdown::defaultTransform($this->text);
    }

    /**
     * {@inheritdoc}
     */
    public function isSupported($fileName)
    {
        $info = pathinfo($fileName);
        $ext = strtolower($info['extension']);

        return in_array($ext, array('md', 'markdown'));
    }
}
