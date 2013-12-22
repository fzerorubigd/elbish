<?php

namespace Cybits\Elbish\Parser;

/**
 * Class Page, a parent for both collection and post
 *
 * @package Cybits\Elbish\Parser
 */
class Page extends Base
{
    protected $isIndexParser = false;

    protected $fileName;
    /**
     * Get if this processor support the file type
     *
     * @param string $fileName file name to check
     *
     * @return boolean
     */
    public static function isSupported($fileName)
    {
        return file_exists($fileName);
    }

    /**
     * Set the loading file name of this parser and determine if this is index page or not
     *
     * @param string $fileName the file name
     */
    protected function setFileName($fileName)
    {
        $this->fileName = $fileName;
        $base = $this->app->getCurrentDir() . '/' . $this->app->getConfig()->get('site.index');
        if ($this->fileName == $base) {
            $this->isIndexParser = true;
        }
    }

    /**
     * Is index file?
     *
     * @return boolean
     */
    public function isIndex()
    {
        return $this->isIndexParser;
    }
}
