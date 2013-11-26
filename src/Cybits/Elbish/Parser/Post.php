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
    /**
     * @param array $postFile post file to load, must be a yaml-front-matter file
     */
    public function __construct($postFile)
    {
        parent::__construct(FrontMatter::parse($postFile));
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
}