<?php

namespace Cybits\Elbish\Template\Engine;

use Cybits\Elbish\Application;
use Cybits\Elbish\Parser\Collection;
use Cybits\Elbish\Parser\Post;
use Cybits\Elbish\Template\Pager;
use Cybits\Elbish\TemplateInterface;

/**
 * Class Environment,
 *
 * @package Cybits\Elbish\Template
 */
class Twig implements TemplateInterface
{
    /** @var  \Twig_Environment */
    protected $twig;

    /** @var  Application */
    protected $app;

    /**
     * Try to render a post in a output file
     *
     * @param Post $post post to render
     *
     * @return string
     */
    public function renderPost(Post $post)
    {
        $twig = $post->get('twig_file', 'post.twig');

        return $this->twig->render($twig, array('post' => $post));
    }

    /**
     * Called when the object is created by application
     *
     * @param Application $app current application
     *
     * @return mixed
     */
    public function init(Application $app)
    {
        $config = $app->getConfig();
        $path = $app->getCurrentDir() . '/' . $config->get('template.path', 'templates');
        if (is_dir($path)) {
            $loader = new \Twig_Loader_Filesystem($path);
        } else {
            $loader = new \Twig_Loader_String();
        }

        $this->twig = new \Twig_Environment($loader);
        $this->twig->addGlobal('elbish', $app);
        $this->twig->addGlobal('config', $config);
        $this->app = $app;
    }

    /**
     * Get the name of this engine, used for identify engine for posts or other thing
     *
     * @return string
     */
    public static function getName()
    {
        return 'twig';
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
        $twig = $collection->get('twig_file', 'collection.twig');

        return $this->twig->render(
            $twig,
            array(
                'collection' => $collection,
                'posts' => $posts,
                'pager' => $pager
            )
        );
    }
}
