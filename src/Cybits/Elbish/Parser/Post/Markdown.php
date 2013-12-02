<?php

namespace Cybits\Elbish\Parser\Post;


use Cybits\Elbish\Parser\Post;

class Markdown extends Post
{
    /**
     * Get the transformed text part of front matter
     *
     * @return string
     */
    public function getText()
    {
        return \Michelf\Markdown::defaultTransform($this->text);
    }

    /**
     * @param $fileName
     *
     * @return bool if the file is supported then load is happen here
     */
    public function isSupported($fileName)
    {
        $info = pathinfo($fileName);
        $ext = strtolower($info['extension']);

        return in_array($ext, array('md', 'markdown'));
    }
}