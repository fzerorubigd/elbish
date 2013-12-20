<?php

namespace Cybits\Elbish\Parser;

use Cybits\Elbish\Application;
use Cybits\Elbish\ApplicationAwareInterface;
use Cybits\Elbish\Exception\GeneralException;
use Cybits\Elbish\Exception\NotSupported;
use RomaricDrigon\MetaYaml\MetaYaml;

/**
 * Class Base
 *
 * @package Cybits\Elbish\Parser
 */
class Base implements \ArrayAccess, \IteratorAggregate, ApplicationAwareInterface
{
    /** @var  array */
    protected $data = array();

    /** @var  Application */
    protected $app;

    /**
     * Load data into the parser
     *
     * @param array   $data  loaded data into an array
     * @param boolean $force force validation on data
     */
    final public function loadData(array $data, $force = true)
    {
        if ($force) {
            if ($schema = $this->loadSchema()) {
                $schema->validate($data);
            }
        }
        $this->data = $data;
    }

    /**
     * Get this file validator
     *
     * @return MetaYaml|boolean
     */
    protected function loadSchema()
    {
        return false;
    }

    /**
     * Flatten the array and get the result
     *
     * @param string $offset  to get
     * @param mixed  $default default if the result not found
     *
     * @return array
     */
    public function get($offset, $default = null)
    {
        $parts = explode('.', $offset);
        $current = & $this->data;
        while ($needle = array_shift($parts)) {
            if (is_array($current) && isset($current[$needle])) {
                $current = & $current[$needle];
            } else {
                return $default;
            }
        }

        return $current;
    }


    /**
     * Flatten the array and get the result
     *
     * @param string $offset to get
     * @param mixed  $value  value to set
     *
     * @throws GeneralException
     */
    public function set($offset, $value)
    {
        $parts = explode('.', $offset);
        $current = & $this->data;
        while ($part = array_shift($parts)) {
            if (is_array($current) && array_key_exists($part, $current)) {
                $current = & $current[$part];
            } elseif (!is_array($current)) {
                throw new GeneralException("Can not set $offset.");
            } else {
                $current[$part] = array();
                $current = & $current[$part];
            }
        }

        $current = $value;
    }

    /**
     * Is this file has the offset or not, identical to offsetExists
     *
     * @param string $offset offset to check
     *
     * @return bool
     */
    public function has($offset)
    {
        $parts = explode('.', $offset);
        $current = & $this->data;
        while ($needle = array_shift($parts)) {
            if (is_array($current) && isset($current[$needle])) {
                $current = & $current[$needle];
            } else {
                return false;
            }
        }

        return true;
    }

    /**
     * Whether a offset exists
     *
     * @param mixed $offset An offset to check for.
     *
     * @return boolean true on success or false on failure.
     * The return value will be casted to boolean if non-boolean was returned.
     *
     * @link http://php.net/manual/en/arrayaccess.offsetexists.php
     */
    public function offsetExists($offset)
    {
        return $this->has($offset);
    }

    /**
     * Offset to retrieve
     *
     * @param mixed $offset The offset to retrieve.
     *
     * @return mixed Can return all value types.
     *
     * @link http://php.net/manual/en/arrayaccess.offsetget.php
     */
    public function offsetGet($offset)
    {
        return $this->get($offset, null);
    }

    /**
     * Offset to set
     *
     * @param mixed $offset The offset to assign the value to.
     * @param mixed $value  The value to set.
     *
     * @throws \Cybits\Elbish\Exception\NotSupported
     * @return void
     *
     * @link http://php.net/manual/en/arrayaccess.offsetset.php
     */
    public function offsetSet($offset, $value)
    {
        $this->set($offset, $value);
    }

    /**
     * Offset to unset
     *
     * @param mixed $offset The offset to unset.
     *
     * @throws \Cybits\Elbish\Exception\NotSupported
     * @return void
     *
     * @link http://php.net/manual/en/arrayaccess.offsetunset.php
     */
    public function offsetUnset($offset)
    {
        throw new NotSupported();
    }


    /**
     * Interface to create an external Iterator.
     *
     * @return \Iterator
     * @link http://php.net/manual/en/class.iteratoraggregate.php
     */
    public function getIterator()
    {
        return new \ArrayIterator($this->data);
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
        $this->app = $app;
    }
}
