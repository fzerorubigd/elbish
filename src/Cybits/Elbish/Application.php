<?php

namespace Cybits\Elbish;

use Composer\Autoload\ClassLoader;
use Cybits\Elbish\Console\Command\BuildPosts;
use Cybits\Elbish\Console\Command\NewPost;
use Cybits\Elbish\Parser\Config;
use Cybits\Elbish\Template\Manager as TemplateManager;
use Cybits\Elbish\Parser\Manager as ParserManager;

/**
 * Class Application
 *
 * @package Cybits\Elbish
 */
class Application extends \Symfony\Component\Console\Application
{
    const VERSION = '0.1.0';
    const APP_NAME = 'elbish, Static publishing system';

    /** @var Config */
    protected $config;

    /** @var   TemplateManager */
    protected $templateManager;

    /** @var  ParserManager */
    protected $parserManager;

    /** @var  Plugin\Loader */
    protected $pluginLoader;

    /** @var  ClassLoader */
    protected $classLoader;

    /**
     * Create new application and add default command and parsers to it
     *
     * @param ClassLoader $classLoader composer loader class to use with plugins
     */
    public function __construct(ClassLoader $classLoader)
    {
        parent::__construct(self::APP_NAME, self::VERSION);
        $this->classLoader = $classLoader;

        $this->templateManager = new TemplateManager($this);
        $this->parserManager = new ParserManager($this);
        // Register default parsers
        $this->pluginLoader = new Plugin\Loader();
        $dir = $this->getCurrentDir() . '/' . $this->getConfig()->get('site.plugin_dir', 'plugins');
        if (is_dir($dir)) {
            $this->loadPlugins($dir);
        }
        //TODO: Support for user wide and system wide plugins
    }

    /**
     * Load plugins from directory
     *
     * @param string $directory directory to load plugin from
     */
    protected function loadPlugins($directory)
    {
        $plugins = $this->pluginLoader->loadPlugins($directory);
        //Add them to composer class map auto-loader
        $this->getClassLoader()->addClassMap($plugins);

        foreach (array_keys($plugins) as $class) {
            $reflection = new \ReflectionClass('\\' . $class);
            if ($reflection->isSubclassOf('\Cybits\Elbish\Parser\Post')) {
                $this->parserManager->registerPostParser($reflection);
            } elseif ($reflection->implementsInterface('\Cybits\Elbish\TemplateInterface')) {
                $this->templateManager->registerEngine($reflection);
            } elseif ($reflection->isSubclassOf('\Cybits\Elbish\Parser\Collection')) {
                $this->parserManager->registerCollectionParser($reflection);
            }
        }
    }

    /**
     * Singleton getInstance method
     *
     * @param \Composer\Autoload\ClassLoader $classLoader current composer class loader
     *
     * @return Application
     */
    public static function createInstance(ClassLoader $classLoader)
    {
        return new self($classLoader);
    }

    /**
     * Get current application config
     *
     * @return Config
     */
    public function getConfig()
    {
        if (!$this->config) {
            if (!file_exists($this->getCurrentDir() . '/config.yaml')) {
                //$output->writeln('<comment>Config file is not available in current directory.</comment>');
                $this->config = new Config(array());
            } else {
                $this->config = new Config($this->getCurrentDir() . '/config.yaml');
            }
        }

        return $this->config;
    }

    /**
     * Get current directory
     *
     * @return string
     */
    public function getCurrentDir()
    {
        return getcwd();
    }

    /**
     * Get current plugin loader
     *
     * @return \Cybits\Elbish\Plugin\Loader
     */
    public function getPluginLoader()
    {
        return $this->pluginLoader;
    }

    /**
     * Get current composer class loader
     *
     * @return \Composer\Autoload\ClassLoader
     */
    public function getClassLoader()
    {
        return $this->classLoader;
    }

    /**
     * Get current template manager
     *
     * @return \Cybits\Elbish\Template\Manager
     */
    public function getTemplateManager()
    {
        return $this->templateManager;
    }

    /**
     * Get parser manager
     *
     * @return \Cybits\Elbish\Parser\Manager
     */
    public function getParserManager()
    {
        return $this->parserManager;
    }
}
