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
     * @param string $configFile config file path
     */
    public function __construct($configFile)
    {
        if (file_exists($configFile)) {
            parent::__construct(Yaml::parse($configFile));
        } else {
            parent::__construct(array(), false);
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