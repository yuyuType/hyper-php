<?php

use Hyper\Data\Enumerator\FilterdIterator;

class FilterdIteratorTest extends PHPUnit_Framework_TestCase
{
    public function testFilterd()
    {
        $iterator = new FilterdIterator(function ($x) { return $x % 2 === 0; }, new ArrayObject(range(1, 5)));
        $this->assertSame([2, 4], iterator_to_array($iterator));
        $this->assertSame(2, $iterator[0]);
        $this->assertSame(4, $iterator[1]);

        $iterator = new FilterdIterator(function ($x) { return $x % 2 === 0; }, new ArrayIterator(range(1, 5)));
        $this->assertSame([2, 4], iterator_to_array($iterator));
        $this->assertSame(2, $iterator[0]);
        $this->assertSame(4, $iterator[1]);

        $iterator = new FilterdIterator(
            function ($x) { return $x % 2 === 0; },
            new ArrayIterator(['val1' => 2, 'val2' => 3]));
        foreach ($iterator as $key => $val) {
            $this->assertSame(0, $key);
        }
    }

    public function testGetIterator()
    {
        $iterator = new FilterdIterator(function ($x) { return $x % 2 === 0; }, new ArrayObject(range(1, 5)));
        $result = [];
        foreach ($iterator as $val) {
            foreach ($iterator as $value) {
                $result[] = $value;
            }
        }
        $this->assertSame([2, 4, 2, 4], $result);

        $iterator = new FilterdIterator(function ($x) { return $x % 2 === 0; }, new ArrayIterator(range(1, 5)));
        $result = [];
        foreach ($iterator as $val) {
            foreach ($iterator as $value) {
                $result[] = $value;
            }
        }
        $this->assertSame([2, 4], $result);
    }

    public function testOffsetSet()
    {
        $iterator = new FilterdIterator(function ($x) { return $x % 2 === 0; }, new ArrayObject(range(1, 5)));
        try {
            $iterator[0] = 3;
        } catch (LogicException $le) {
            $this->assertInstanceOf("LogicException", $le);
        }
    }

    public function testOffsetExists()
    {
        $iterator = new FilterdIterator(function ($x) { return $x % 2 === 0; }, new ArrayObject(range(1, 5)));
        $this->assertTrue(isset($iterator[0]));
        $this->assertTrue(isset($iterator[1]));
        $this->assertFalse(isset($iterator[2]));
        $this->assertFalse(isset($iterator[3]));
        $this->assertFalse(isset($iterator[4]));
        $this->assertFalse(isset($iterator[5]));

        $iterator = new FilterdIterator(function ($x) { return $x % 2 === 0; }, new ArrayIterator(range(1, 5)));
        $this->assertTrue(isset($iterator[0]));
        $this->assertTrue(isset($iterator[1]));
        $this->assertFalse(isset($iterator[2]));
        $this->assertFalse(isset($iterator[3]));
        $this->assertFalse(isset($iterator[4]));
        $this->assertFalse(isset($iterator[5]));
        $this->assertTrue(isset($iterator[0]));
        $this->assertTrue(isset($iterator[1]));
        $this->assertFalse(isset($iterator[2]));
        $this->assertFalse(isset($iterator[3]));
        $this->assertFalse(isset($iterator[4]));
        $this->assertFalse(isset($iterator[5]));
    }

    public function testOffsetUnset()
    {
        $iterator = new FilterdIterator(function ($x) { return $x % 2 === 0; }, new ArrayObject(range(1, 5)));
        try {
            unset($iterator[0]);
        } catch (LogicException $le) {
            $this->assertInstanceOf("LogicException", $le);
        }
    }

    public function testOffsetGet()
    {
        $iterator = new FilterdIterator(function ($x) { return $x % 2 === 0; }, new ArrayObject(range(1, 5)));
        $this->assertSame(4, $iterator[1]);

        $iterator = new FilterdIterator(function ($x) { return $x % 2 === 0; }, new ArrayIterator(range(1, 5)));
        $this->assertSame(4, $iterator[1]);
        $this->assertSame(4, $iterator[1]);
        $this->assertSame(2, $iterator[0]);
        $this->assertSame(2, $iterator[0]);
    }

    public function testCount()
    {
        $iterator = new FilterdIterator(function ($x) { return $x % 2 === 0; }, new ArrayObject(range(1, 5)));
        $this->assertSame(2, count($iterator));
    }
}
