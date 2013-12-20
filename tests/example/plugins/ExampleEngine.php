<?php

namespace ExampleParser;


use Cybits\Elbish\Application;
use Cybits\Elbish\Parser\Collection;
use Cybits\Elbish\Parser\Post;
use Cybits\Elbish\Template\Pager;
use Cybits\Elbish\TemplateInterface;

/**
 * Class ExampleEngine
 *
 * @package ExampleParser
 */
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
        return ":::" . $post->getText() . ":::";
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

    /**
     * Render a collection
     *
     * @param Collection $collection collection to render
     * @param array      $posts      posts in collection
     * @param Pager      $pager      pager
     *
     * @return string
     */
    public function renderCollection(Collection $collection, array $posts, Pager $pager)
    {
        // TODO: Implement renderCollection() method.
    }
}
