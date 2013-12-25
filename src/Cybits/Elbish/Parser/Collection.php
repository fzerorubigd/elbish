<?php

namespace Cybits\Elbish\Parser;

use Cybits\Elbish\Template\Pager;
use RomaricDrigon\MetaYaml\MetaYaml;
use Symfony\Component\Yaml\Yaml;

/**
 * Class Collection
 *
 * @package Cybits\Elbish\Parser
 */
class Collection extends Page
{
    /**
     * Load data from yaml file
     *
     * @param string $fileName yaml file
     */
    public function loadYamlFile($fileName)
    {
        $this->setFileName($fileName);
        $data = Yaml::parse($fileName);
        $this->loadData($data);
    }

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

    /**
     * Get item per page for this collection
     *
     * @return integer
     */
    public function getPerPage()
    {
        $result = $this->app->getConfig()->get('collection.per_page', 10);
        if ($this->has('per_page')) {
            $result = $this->get('per_page');
        }

        return $result;
    }

    /**
     * Render current post
     *
     * @param Post[] $posts array of posts in this collection
     *
     * @return string[]
     */
    public function render(array $posts)
    {
        $engine = $this->app->getConfig()->get('template.default_engine', 'twig');
        if ($this['template_engine']) {
            $engine = $this['template_engine'];
        }

        $result = array();
        $page = 0;
        $perPage = $this->getPerPage();
        $total = $remain = count($posts);
        $pager = new Pager($total, $this['_url'], $perPage);
        while ($remain > 0) {
            $currentPosts = array_slice($posts, $page * $perPage, $perPage);
            $page++;
            $pager->setCurrentPage($page);
            $remain -= $page * $perPage;
            $result[$page] = $this->app
                ->getTemplateManager()
                ->getEngine($engine)
                ->renderCollection($this, $currentPosts, $pager);
        }

        return $result;
    }
}
