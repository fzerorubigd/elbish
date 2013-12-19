<?php

namespace Testing;

/**
 * Class TestingBootstrap, I need to save composer auto-loader for using
 * in Application class instance
 *
 * @package Testing
 */
class TestingBootstrap
{
    protected static $autoLoader = null;

    /**
     * Get composer auto loader
     *
     * @return \Composer\Autoload\ClassLoader
     */
    public static function getLoader()
    {
        if (!self::$autoLoader) {
            self::$autoLoader = include __DIR__ . "/../vendor/autoload.php";
        }

        return self::$autoLoader;
    }
}
