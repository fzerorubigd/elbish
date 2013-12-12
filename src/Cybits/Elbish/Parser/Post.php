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
class Post extends Base
{
    protected $text;

    public function loadFrontMatter($fileName)
    {
        if (!$this->isSupported($fileName)) {
            return false;
        }

        try {
            $fm = FrontMatter::parse($fileName);
            $this->text = $fm['text'];
            $this->loadData($fm['yaml']);
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
     * @param $fileName
     *
     * @return bool if the file is supported then load is happen here
     */
    public function isSupported($fileName)
    {
        // This default support every file type, so work as a fallback.
        return true;
    }
}