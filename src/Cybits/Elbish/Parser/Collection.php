<?php

namespace Cybits\Elbish\Parser;

use RomaricDrigon\MetaYaml\MetaYaml;

/**
 * Class Collection
 *
 * @package Cybits\Elbish\Parser
 */
class Collection extends Base
{

    /**
     * Get this file validator
     *
     * @return MetaYaml
     */
    protected function loadSchema()
    {
        //TODO : better loading support, prevent using __DIR__, using a loader class maybe?
        $schemaFile = realpath(__DIR__ . '/../Schema/collection.yaml');
        $schema = new MetaYaml(Yaml::parse($schemaFile), true);

        return $schema;
    }

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
        return true;
    }
}
