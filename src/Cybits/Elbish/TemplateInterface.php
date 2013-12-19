<?php

namespace Cybits\Elbish;

use Cybits\Elbish\Parser\Post;

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
     * Get the name of this engine, used for identify engine for posts or other thing
     *
     * @return string
     */
    public static function getName();
}
