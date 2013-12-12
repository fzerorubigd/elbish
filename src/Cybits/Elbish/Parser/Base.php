<?php

namespace Cybits\Elbish\Parser;

use Cybits\Elbish\Exception\NotSupported;
use RomaricDrigon\MetaYaml\MetaYaml;

abstract class Base implements \ArrayAccess, \IteratorAggregate
{
    /** @var  array */
    protected $data;

    /**
     * Load data into the parser
     *
     * @param array   $data  loaded data into an array
     * @param boolean $force force validation on data
     */
    final public function loadData(array $data, $force = true)
    {
        if ($force) {
            $schema = $this->loadSchema();
            $schema->validate($data);
        }
        $this->data = $data;
    }

    /**
     * Get this file validator
     *
     * @return MetaYaml
     */
    abstract protected function loadSchema();

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
     * (PHP 5 &gt;= 5.0.0)<br/>
     * Whether a offset exists
     *
     * @link http://php.net/manual/en/arrayaccess.offsetexists.php
     *
     * @param mixed $offset <p>
     *                      An offset to check for.
     *                      </p>
     *
     * @return boolean true on success or false on failure.
     * </p>
     * <p>
     * The return value will be casted to boolean if non-boolean was returned.
     */
    public function offsetExists($offset)
    {
        return $this->has($offset);
    }

    /**
     * (PHP 5 &gt;= 5.0.0)<br/>
     * Offset to retrieve
     *
     * @link http://php.net/manual/en/arrayaccess.offsetget.php
     *
     * @param mixed $offset <p>
     *                      The offset to retrieve.
     *                      </p>
     *
     * @return mixed Can return all value types.
     */
    public function offsetGet($offset)
    {
        return $this->get($offset, null);
    }

    /**
     * (PHP 5 &gt;= 5.0.0)<br/>
     * Offset to set
     *
     * @link http://php.net/manual/en/arrayaccess.offsetset.php
     *
     * @param mixed $offset <p>
     *                      The offset to assign the value to.
     *                      </p>
     * @param mixed $value  <p>
     *                      The value to set.
     *                      </p>
     *
     * @throws \Cybits\Elbish\Exception\NotSupported
     * @return void
     */
    public function offsetSet($offset, $value)
    {
        throw new NotSupported();
    }

    /**
     * (PHP 5 &gt;= 5.0.0)<br/>
     * Offset to unset
     *
     * @link http://php.net/manual/en/arrayaccess.offsetunset.php
     *
     * @param mixed $offset <p>
     *                      The offset to unset.
     *                      </p>
     *
     * @throws \Cybits\Elbish\Exception\NotSupported
     * @return void
     */
    public function offsetUnset($offset)
    {
        throw new NotSupported();
    }


    /**
     * Interface to create an external Iterator.
     *
     * @link http://php.net/manual/en/class.iteratoraggregate.php
     */
    public function getIterator()
    {
        return new \ArrayIterator($this->data);
    }
}