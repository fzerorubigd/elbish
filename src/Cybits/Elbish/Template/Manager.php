<?php

namespace Cybits\Elbish\Template;

use Cybits\Elbish\Application;
use Cybits\Elbish\Exception\NotFound;
use Cybits\Elbish\TemplateInterface;

/**
 * Class Manager
 *
 * @package Cybits\Elbish\Template
 */
class Manager
{
    /** @var  TemplateInterface[] */
    private $engines = array();

    /** @var  Application */
    private $app;

    /**
     * Create new manager
     *
     * @param Application $app current application
     */
    public function __construct(Application $app)
    {
        $this->app = $app;
        $this->registerEngine(
            new \ReflectionClass('\Cybits\Elbish\Template\Engine\Twig')
        );

    }

    /**
     * Register new engine
     *
     * @param \ReflectionClass $engine engine to register
     */
    public function registerEngine(\ReflectionClass $engine)
    {
        $getName = $engine->getMethod('getName');
        $name = $getName->invoke(null);

        $this->engines[$name] = $engine->newInstance();
        $this->engines[$name]->init($this->app);
    }

    /**
     * Get engine base on name
     *
     * @param string $name name of engine
     *
     * @throws NotFound
     * @return \Cybits\Elbish\TemplateInterface
     */
    public function getEngine($name)
    {
        if (isset($this->engines[$name])) {
            return $this->engines[$name];
        }

        throw new NotFound("No engine registered for $name.");
    }
}
