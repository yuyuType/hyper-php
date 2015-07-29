<?php

use Hyper\Data\Collections\MapIterator;

class MapIteratorTest extends PHPUnit_Framework_TestCase
{
    public function testMap()
    {
        $iterator = new MapIterator(function ($x) { return $x * 2; }, new ArrayObject(range(1, 5)));
        $this->assertSame([2, 4, 6, 8, 10], iterator_to_array($iterator));
        $this->assertSame(2, $iterator[0]);
        $this->assertSame(4, $iterator[1]);
        $this->assertSame(6, $iterator[2]);
        $this->assertSame(8, $iterator[3]);
        $this->assertSame(10, $iterator[4]);

        $iterator = new MapIterator(function ($x) { return $x * 2; }, new ArrayIterator(range(1, 5)));
        $this->assertSame([2, 4, 6, 8, 10], iterator_to_array($iterator));
        $this->assertSame(2, $iterator[0]);
        $this->assertSame(4, $iterator[1]);
        $this->assertSame(6, $iterator[2]);
        $this->assertSame(8, $iterator[3]);
        $this->assertSame(10, $iterator[4]);

        $iterator = new MapIterator(
            function ($x) { return $x * 2; },
            new ArrayIterator(['val1' => 2, 'val2' => 3]));
        $keys = [];
        $values = [];
        foreach ($iterator as $key => $val) {
            $keys[] = $key;
            $values[] = $val;
        }
        $this->assertSame([0, 1], $keys);
        $this->assertSame([4, 6], $values);
    }

    public function testGetIterator()
    {
        $iterator = new MapIterator(function ($x) { return $x * 2; }, new ArrayObject(range(1, 3)));
        $result = [];
        foreach ($iterator as $val) {
            foreach ($iterator as $value) {
                $result[] = $value;
            }
        }
        $this->assertSame([2, 4, 6, 2, 4, 6, 2, 4, 6], $result);

        $iterator = new MapIterator(function ($x) { return $x * 2; }, new ArrayIterator(range(1, 3)));
        $result = [];
        foreach ($iterator as $val) {
            foreach ($iterator as $value) {
                $result[] = $value;
            }
        }
        $this->assertSame([2, 4, 6], $result);
    }

    public function testOffsetSet()
    {
        $iterator = new MapIterator(function ($x) { return $x * 2; }, new ArrayObject(range(1, 5)));
        try {
            $iterator[0] = 3;
        } catch (LogicException $le) {
            $this->assertInstanceOf("LogicException", $le);
        }
    }

    public function testOffsetExists()
    {
        $iterator = new MapIterator(function ($x) { return $x * 2; }, new ArrayObject(range(1, 5)));
        $this->assertTrue(isset($iterator[4]));
        $this->assertFalse(isset($iterator[5]));
    }

    public function testOffsetUnset()
    {
        $iterator = new MapIterator(function ($x) { return $x * 2; }, new ArrayObject(range(1, 5)));
        try {
            unset($iterator[0]);
        } catch (LogicException $le) {
            $this->assertInstanceOf("LogicException", $le);
        }
    }

    public function testOffsetGet()
    {
        $iterator = new MapIterator(function ($x) { return $x * 2; }, new ArrayObject(range(1, 5)));
        $this->assertSame(2, $iterator[0]);
    }

    public function testCount()
    {
        $iterator = new MapIterator(function ($x) { return $x * 2; }, new ArrayObject(range(1, 5)));
        $this->assertSame(5, count($iterator));
    }
}
