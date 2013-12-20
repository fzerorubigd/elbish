<?php

namespace ExampleParser;

use Cybits\Elbish\Parser\Collection;
use Cybits\Elbish\Parser\Post;

/**
 * Class ExampleParser
 *
 * @package ExampleParser
 */
class ExampleCollection extends Collection
{
    /**
     * Compare two post for create order list
     * $post1 equal to $post2 return 0
     * If $post1 is greater than $post2 return >0 number
     * If $post2 is greater than $post1 return <0 number
     *
     * @param Post $first  first post
     * @param Post $second second post
     *
     * @return integer
     */
    public function compare(Post $first, Post $second)
    {
        //Normal compare is base on date
        return $first->getDate() - $second->getDate();
    }

    /**
     * Is this post included in this collection or not?
     *
     * @param Post $post post to check
     *
     * @return bool
     */
    public function isIncluded(Post $post)
    {
        // This is the default behavior, any post is accepted.
        return isset($post['example']) && $post['example'] == 'yes';
    }

    /**
     * Get if this processor support the file type
     *
     * @param string $fileName file name to check
     *
     * @return boolean
     */
    public static function isSupported($fileName)
    {
        $info = pathinfo($fileName);
        $ext = strtolower($info['extension']);

        return in_array($ext, array('custom'));
    }
}
