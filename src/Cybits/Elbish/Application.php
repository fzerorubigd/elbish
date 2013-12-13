<?php

namespace Cybits\Elbish;

use Cybits\Elbish\Console\Command\BuildPosts;
use Cybits\Elbish\Console\Command\NewPost;
use Cybits\Elbish\Parser\Config;
use Cybits\Elbish\Parser\Post;

/**
 * Class Application
 *
 * @package Cybits\Elbish
 */
class Application extends \Symfony\Component\Console\Application
{
    protected static $instance;

    const VERSION = '0.1.0';
    const APP_NAME = 'elbish, Static publishing system';

    /** @var Config */
    protected $config;

    /** @var  \Twig_Environment */
    protected $twig;

    /** @var  Parser\Post[] */
    protected $parsers = array();

    /**
     * Create new application and add default command and parsers to it
     */
    public function __construct()
    {
        parent::__construct(self::APP_NAME, self::VERSION);

        $this->add(new NewPost());
        $this->add(new BuildPosts());


        // Register default parsers
        // Post parser is available if there is no other parser is there
        $this->registerParser(new Post());
        $this->registerParser(new Post\Markdown());
    }

    /**
     * Singleton getInstance method
     *
     * @return Application
     */
    public static function getInstance()
    {
        if (!self::$instance) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    /**
     * Init twig env
     *
     * @deprecated
     */
    protected function initTwig()
    {
        if (!$this->twig) {
            $loader = new \Twig_Loader_Filesystem(
                $this->getCurrentDir() . '/' . $this->getConfig()->get('twig.path', 'templates')
            );
            $this->twig = new \Twig_Environment($loader);
            $this->twig->addGlobal('elbish', $this);
            $this->twig->addGlobal('config', $this->getConfig());
        }
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
                $this->config = new Config($this->currentDir . '/config.yaml');
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
     * Register a new parser object
     *
     * @param Post $parser the parser object
     */
    public function registerParser(Post $parser)
    {
        array_unshift($this->parsers, $parser);
    }

    /**
     * Get the parser for file
     *
     * @param string $filename file name to parse
     *
     * @return Post
     */
    public function getParserForFile($filename)
    {
        $result = null;
        foreach ($this->parsers as $parser) {
            if ($parser->isSupported($filename)) {
                $result = $parser;
                break;
            }
        }

        return $result;
    }

    /**
     * Get template env.
     *
     * @return \Twig_Environment
     * @deprecated
     */
    public function getTwig()
    {
        $this->initTwig();

        return $this->twig;
    }
}
