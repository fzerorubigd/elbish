<?php

namespace Cybits\Elbish\Template;

/**
 * Class Pager
 *
 * @package Cybits\Elbish\Template
 */
class Pager
{
    protected $currentPage;

    protected $perPage;

    protected $total;

    protected $pattern;

    /**
     * Create a pager
     *
     * @param integer $total   total pages
     * @param integer $perPage item per page
     */
    public function __construct($total, $perPage = 10)
    {
        $this->total = $total;
        $this->currentPage = 1;
        $this->perPage = $perPage;
    }

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
}
