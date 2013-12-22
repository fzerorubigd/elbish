<?php

namespace Cybits\Elbish\Parser;

use Cybits\Yaml\FrontMatter;
use RomaricDrigon\MetaYaml\MetaYaml;
use Symfony\Component\Yaml\Yaml;

/**
 * Class Post
 *
 * @package Cybits\Elbish\Parser
 */
class Post extends Page
{
    protected $text;

    /**
     * Load a front matter file, if this class not support that file has any problem then
     * the result is false.
     *
     * @param string $fileName file name to load
     *
     * @return bool
     */
    public function loadFrontMatter($fileName)
    {
        if (!$this->isSupported($fileName)) {
            return false;
        }
        $this->setFileName($fileName);
        try {
            $frontMatter = FrontMatter::parse($fileName);
            $this->text = $frontMatter['text'];
            $this->loadData($frontMatter['yaml']);
        } catch (\Exception $e) {
            return false;
        }

        return true;
    }

    /**
     * Get the transformed text part of front matter
     *
     * @return string
     */
    public function getText()
    {
        return $this->text;
    }

    /**
     * Get this file validator
     *
     * @return MetaYaml
     */
    protected function loadSchema()
    {
        //TODO : better loading support, prevent using __DIR__, using a loader class maybe?
        $schemaFile = realpath(__DIR__ . '/../Schema/post.yaml');
        $schema = new MetaYaml(Yaml::parse($schemaFile), true);

        return $schema;
    }

    /**
     * Get the post date, base on meta data or file ctime
     *
     * @return int timestamp
     */
    public function getDate()
    {
        if (isset($this['date'])) {
            return strtotime($this['date']);
        } else {
            $file = new \SplFileInfo($this->fileName);

            // Try to load it from file time, not a good way, but what can I do??
            return $file->getCTime();
        }
    }

    /**
     * Render current post
     *
     * @return string
     */
    public function render()
    {
        $engine = $this->app->getConfig()->get('template.default_engine', 'twig');
        if ($this['template_engine']) {
            $engine = $this['template_engine'];
        }

        return $this->app->getTemplateManager()->getEngine($engine)->renderPost($this);
    }
}
