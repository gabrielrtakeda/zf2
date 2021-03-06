<?php
/**
 * Zend Framework
 *
 * LICENSE
 *
 * This source file is subject to the new BSD license that is bundled
 * with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://framework.zend.com/license/new-bsd
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@zend.com so we can send you a copy immediately.
 *
 * @category   Zend
 * @package    Zend_Db
 * @subpackage Adapter
 * @copyright  Copyright (c) 2005-2012 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */

namespace Zend\Db\Adapter;

/**
 * @category   Zend
 * @package    Zend_Db
 * @subpackage Adapter
 * @copyright  Copyright (c) 2005-2012 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class ParameterContainer implements \Iterator, \ArrayAccess
{

    const TYPE_AUTO    = 'auto';
    const TYPE_NULL    = 'null';
    const TYPE_DOUBLE  = 'double';
    const TYPE_INTEGER = 'integer';
    const TYPE_STRING  = 'string';
    const TYPE_LOB     = 'lob';

    /**
     * Data
     * 
     * @var array
     */
    protected $data = array();

    /**
     * @var array
     */
    protected $positions = array();

    /**
     * Errata
     * 
     * @var array 
     */
    protected $errata = array();

    /**
     * Constructor
     * 
     * @param array $data 
     */
    public function __construct(array $data = array())
    {
        if ($data) {
            $this->setFromArray($data);
        }
    }

    /**
     * Offset exists
     * 
     * @param  string $name
     * @return boolean 
     */
    public function offsetExists($name)
    {
        return (isset($this->data[$name]));
    }

    /**
     * Offset get
     * 
     * @param  string $name
     * @return mixed 
     */
    public function offsetGet($name)
    {
        return (isset($this->data[$name])) ? $this->data[$name] : null;
    }

    /**
     * @param $name
     * @param $from
     */
    public function offsetSetReference($name, $from)
    {
        $this->data[$name] =& $this->data[$from];
    }
    
    /**
     * Offset set
     * 
     * @param string|integer $name
     * @param mixed $value
     * @param mixed $errata 
     */
    public function offsetSet($name, $value, $errata = null)
    {
        $this->data[$name] = $value;

        $names = array_keys($this->data);
        $this->positions[array_search($name, $names)] = $name;

        if ($errata) {
            $this->offsetSetErrata($name, $errata);
        }
    }

    /**
     * Offset unset
     * 
     * @param  string $name
     * @return ParameterContainer 
     */
    public function offsetUnset($name)
    {
        if (is_int($name)) {
            $name = $this->positions[$name];
        }
        unset($this->data[$name]);
        return $this;
    }

    /**
     * Set from array
     * 
     * @param  array $data
     * @return ParameterContainer 
     */
    public function setFromArray(Array $data)
    {
        foreach ($data as $n => $v) {
            $this->offsetSet($n, $v);
        }
        return $this;
    }

    /**
     * Offset set errata
     * 
     * @param string|integer $name
     * @param mixed $errata 
     */
    public function offsetSetErrata($name, $errata)
    {
        if (is_int($name)) {
            $name = $this->positions[$name];
        }
        $this->errata[$name] = $errata;
    }
    
    /**
     * Offset get errata
     * 
     * @param  string|integer $name
     * @return mixed 
     */
    public function offsetGetErrata($name)
    {
        if (is_int($name)) {
            $name = $this->positions[$name];
        }
        if (!array_key_exists($name, $this->data)) {
            throw new \InvalidArgumentException('Data does not exist for this name/position');
        }
        return $this->errata[$name];
    }

    /**
     * Offset has errata
     * 
     * @param  string|integer $name
     * @return boolean 
     */
    public function offsetHasErrata($name)
    {
        if (is_int($name)) {
            $name = $this->positions[$name];
        }
        return (isset($this->errata[$name]));
    }

    /**
     * Offset unset errata
     * 
     * @param string|integer $name
     */
    public function offsetUnsetErrata($name)
    {
        if (is_int($name)) {
            $name = $this->positions[$name];
        }
        if (!array_key_exists($name, $this->errata)) {
            throw new \InvalidArgumentException('Data does not exist for this name/position');
        }
        $this->errata[$name] = null;
    }

    /**
     * Get errata iterator
     * 
     * @return \ArrayIterator 
     */
    public function getErrataIterator()
    {
        return new \ArrayIterator($this->errata);
    }

    /**
     * getNamedArray
     *
     * @return array
     */
    public function getNamedArray()
    {
        return $this->data;
    }

    /**
     * getNamedArray
     *
     * @return array
     */
    public function getPositionalArray()
    {
        return array_values($this->data);
    }

    /**
     * count
     * 
     * @return integer 
     */
    public function count()
    {
        return count($this->data);
    }

    /**
     * Current
     * 
     * @return mixed 
     */
    public function current()
    {
        return current($this->data);
    }

    /**
     * Next
     * 
     * @return mixed 
     */    
    public function next()
    {
        return next($this->data);
    }

    /**
     * Key
     * 
     * @return mixed 
     */
    public function key()
    {
        return key($this->data);
    }

    /**
     * Valid
     * 
     * @return boolean 
     */
    public function valid()
    {
        return (current($this->data) !== false);
    }

    /**
     * Rewind
     */
    public function rewind()
    {
        reset($this->data);
    }

    /**
     * @param array $array
     * @return ParameterContainer
     */
    public function merge(array $array)
    {
        foreach ($array as $key => $value) {
            if (is_int($key)) {
                $key = null;
            }
            $this->offsetSet($key, $value);
        }
        return $this;
    }
}
