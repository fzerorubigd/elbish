<?php

namespace ExampleParser;


use Cybits\Elbish\Application;
use Cybits\Elbish\Parser\Post;
use Cybits\Elbish\TemplateInterface;

class ExampleEngine implements TemplateInterface
{

    /** @var  Application */
    private $app;
    /**
     * Called when the object is created by application
     *
     * @param Application $app current application
     *
     * @return mixed
     */
    public function init(Application $app)
    {
        $this->app = $app;
    }

    /**
     * Try to render a post in a output file
     *
     * @param Post $post post to render
     *
     * @return string
     */
    public function renderPost(Post $post)
    {
        return ":::" . $post->getDate() . ":::";
    }

    /**
     * Get the name of this engine, used for identify engine for posts or other thing
     *
     * @return string
     */
    public static function getName()
    {
        return 'fun';
    }
}
