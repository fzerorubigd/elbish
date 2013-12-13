<?php

namespace Cybits\Elbish\Parser;


use RomaricDrigon\MetaYaml\MetaYaml;
use Symfony\Component\Yaml\Yaml;

/**
 * Class Config, default config parser for elbish
 *
 * @package Cybits\Elbish\Parser
 */
class Config extends Base
{
    /**
     * Create a new config base on file or config array
     *
     * @param string|array $config config file path or loaded config
     */
    public function __construct($config = array())
    {
        if (is_string($config) && file_exists($config)) {
            $this->loadData(Yaml::parse($config));
        } else {
            $this->loadData((array)$config, false);
        }
    }

    /**
     * Get this file validator
     *
     * @return MetaYaml
     */
    protected function loadSchema()
    {
        //TODO : better loading support, prevent using __DIR__, using a loader class maybe?
        $schemaFile = realpath(__DIR__ . '/../Schema/config.yaml');
        $schema = new MetaYaml(Yaml::parse($schemaFile), true);

        return $schema;
    }
}