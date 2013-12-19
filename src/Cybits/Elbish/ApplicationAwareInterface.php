<?php

namespace Cybits\Elbish;

/**
 * Interface ApplicationAwareInterface
 *
 * @package Cybits\Elbish
 */
interface ApplicationAwareInterface
{
    /**
     * Called when the object is created by application
     *
     * @param Application $app current application
     *
     * @return mixed
     */
    public function init(Application $app);
}
