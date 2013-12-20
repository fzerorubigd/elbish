<?php

namespace Cybits\Elbish;

use Cybits\Elbish\Parser\Collection;
use Cybits\Elbish\Parser\Post;
use Cybits\Elbish\Template\Pager;

/**
 * Interface TemplateInterface
 *
 * @package Cybits\Elbish
 */
interface TemplateInterface extends ApplicationAwareInterface
{
    /**
     * Try to render a post in a output file
     *
     * @param Post $post post to render
     *
     * @return string
     */
    public function renderPost(Post $post);

    /**
     * Render a collection
     *
     * @param Collection $collection collection to render
     * @param array      $posts      posts in collection
     * @param Pager      $pager      pager
     *
     * @return string
     */
    public function renderCollection(Collection $collection, array $posts, Pager $pager);

    /**
     * Get the name of this engine, used for identify engine for posts or other thing
     *
     * @return string
     */
    public static function getName();
}
