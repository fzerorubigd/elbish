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
abstract class Post extends Base
{
    protected $text;
    /**
     * @param array $postFile post file to load, must be a yaml-front-matter file
     */
    public function __construct($postFile)
    {
        $fm = FrontMatter::parse($postFile);
        $this->text = $fm['text'];
        parent::__construct($fm['yaml']);
    }

    /**
     * Get the transformed text part of front matter
     *
     * @return string
     */
    abstract public function getText();
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
    abstract public function isSupported($fileName);
}