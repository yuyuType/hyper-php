<?php

namespace Hyper\Data\Collections;

class StringIterator implements \IteratorAggregate, \ArrayAccess, \Countable {
    private $str;

    public function __construct($str) {
        $this->str = $str;
    }

    public function getIterator() {
        return new \ArrayIterator(preg_split('//u', $this->str, -1, PREG_SPLIT_NO_EMPTY));
    }

    public function offsetSet($offset, $value) {
        $this->str[$offset] = $value;
    }

    public function offsetExists($offset) {
        return isset($this->str[$offset]);
    }

    public function offsetUnset($offset) {
        unset($this->str[$offset]);
    }

    public function offsetGet($offset) {
        return $this->str[$offset];
    }

    public function count() {
        return mb_strlen($this->str);
    }
}
