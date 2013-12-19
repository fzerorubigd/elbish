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
    protected static $autoloader = null;

    /**
     * Get composer auto loader
     *
     * @return mixed
     */
    public static function getLoader()
    {
        if (!self::$autoloader) {
            self::$autoloader = include __DIR__ . "/../vendor/autoload.php";
        }

        return self::$autoloader;
    }
}


