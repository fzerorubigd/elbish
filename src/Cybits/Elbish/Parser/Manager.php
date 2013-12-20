<?php

namespace Cybits\Elbish\Parser;

use Cybits\Elbish\Application;

/**
 * Class Manager, Parser manager
 *
 * @package Cybits\Elbish\Parser
 */
class Manager
{
    /** @var  Application */
    protected $app;

    /** @var  \ReflectionClass[] */
    private $postParsers = array();

    /** @var \ReflectionClass[] */
    private $collectionParser = array();

    /**
     * Create new manager
     *
     * @param Application $app current application
     */
    public function __construct(Application $app)
    {
        $this->app = $app;
        // Default parsers
        $this->registerPostParser(new \ReflectionClass('\Cybits\Elbish\Parser\Post'));
        $this->registerPostParser(new \ReflectionClass('\Cybits\Elbish\Parser\Post\Markdown'));
        $this->registerCollectionParser(new \ReflectionClass('\Cybits\Elbish\Parser\Collection'));
    }

    /**
     * Register new post parser
     *
     * @param \ReflectionClass $parser the parser reflection class
     */
    public function registerPostParser(\ReflectionClass $parser)
    {
        array_unshift($this->postParsers, $parser);
    }

    /**
     * Get the post parser for file
     *
     * @param string $filename file name to parse
     *
     * @return Post
     */
    public function getParserForPostFile($filename)
    {
        $result = null;
        foreach ($this->postParsers as $parser) {
            $isSupported = $parser->getMethod('isSupported');
            if ($isSupported->isStatic() && $isSupported->invoke(null, $filename)) {
                $result = $parser->newInstance();
                $result->init($this->app);
                break;
            }
        }

        return $result;
    }

    /**
     * Register new collection parser
     *
     * @param \ReflectionClass $parser parser to add
     */
    public function registerCollectionParser(\ReflectionClass $parser)
    {
        array_unshift($this->collectionParser, $parser);
    }


    /**
     * Get the post parser for file
     *
     * @param string $filename file name to parse
     *
     * @return Collection
     */
    public function getParserForCollectionFile($filename)
    {
        $result = null;
        foreach ($this->collectionParser as $parser) {
            $isSupported = $parser->getMethod('isSupported');
            if ($isSupported->isStatic() && $isSupported->invoke(null, $filename)) {
                $result = $parser->newInstance();
                $result->init($this->app);
                break;
            }
        }

        return $result;
    }
}
