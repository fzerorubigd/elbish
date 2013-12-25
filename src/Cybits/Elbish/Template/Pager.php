<?php

namespace Cybits\Elbish\Template;

use Traversable;

/**
 * Class Pager
 *
 * @package Cybits\Elbish\Template
 */
class Pager implements \IteratorAggregate
{
    protected $currentPage;

    protected $perPage;

    protected $total;

    protected $pattern;

    protected $data;

    protected $range = 3;

    /**
     * Set current page
     *
     * @param integer $page the page number
     */
    public function setCurrentPage($page)
    {
        $this->currentPage = $page;
    }

    /**
     * Get current page
     *
     * @return integer
     */
    public function getCurrentPage()
    {
        return $this->currentPage;
    }

    /**
     * Get target pattern
     *
     * @return string
     */
    public function getPattern()
    {
        return $this->pattern;
    }

    /**
     * Set target pattern
     *
     * @param string $pattern the current pattern must contain %d
     */
    public function setPattern($pattern)
    {
        $this->pattern = $pattern;
    }

    /**
     * Create a pager
     *
     * @param integer $total   total pages
     * @param string  $pattern link pattern
     * @param integer $perPage item per page
     */
    public function __construct($total, $pattern, $perPage = 10)
    {
        $this->total = $total;
        $this->currentPage = 1;
        $this->perPage = $perPage;
        $this->pattern = $pattern;
        $this->range = 3;
    }

    /**
     * Build the pagination array
     */
    private function build()
    {
        $totalPage = intval($this->total / $this->perPage);
        if ($this->total % $this->perPage) {
            $totalPage++;
        }
        $keys = range(1, $totalPage);
        $this->data = array();
        $lastKey = 0;
        foreach ($keys as $key) {
            if ($key <= $this->range ||
                ( $key > $this->currentPage - $this->range && $key < $this->currentPage + $this->range) ||
                $key > $totalPage - $this->range
            ) {
                if ($key - $lastKey > 1) {
                    $this->data[] = array (
                        'number' => -1,
                        'sep' => true
                    );
                }
                $lastKey = $key;
                $this->data[] = array(
                    'number' => $key,
                    'link' => $this->getTarget($key),
                    'current' => $key == $this->currentPage
                );
            }
        }

    }

    /**
     * Get target path for the current page
     *
     * @param integer $page the page number
     *
     * @return string
     */
    public function getTarget($page)
    {
        return strtr($this->getPattern(), array(':page' => $page));
    }

    /**
     * (PHP 5 &gt;= 5.0.0)<br/>
     * Retrieve an external iterator
     *
     * @link http://php.net/manual/en/iteratoraggregate.getiterator.php
     * @return Traversable An instance of an object implementing <b>Iterator</b> or
     *       <b>Traversable</b>
     */
    public function getIterator()
    {
        $this->build();

        return new \ArrayIterator($this->data);
    }
}
