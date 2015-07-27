<?php

use Hyper\Data\Option;
use Hyper\Enumerable;

class EnumerableTest extends PHPUnit_Framework_TestCase
{
    public function testHead()
    {
        $arr = ['val' => 1, 'val1' => 2];
        $this->assertEquals(Option::Some(1), Enumerable::head($arr));
        $this->assertEquals(Option::None(), Enumerable::head([]));

        $it = new ArrayIterator(range(1, 10));
        $this->assertEquals(Option::Some(1), Enumerable::head($it));
        $this->assertEquals(Option::Some(1), Enumerable::head($it));

        $this->assertEquals(Option::Some("t"), Enumerable::head("test"));
        $this->assertEquals(Option::None(), Enumerable::head(""));
    }

    public function testLast()
    {
        $arr = ['val' => 1, 'val1' => 2];
        $this->assertEquals(Option::Some(2), Enumerable::last($arr));
        $this->assertEquals(Option::None(), Enumerable::last([]));

        $it = new ArrayIterator(range(1, 10));
        $this->assertEquals(Option::Some(10), Enumerable::last($it));
        $this->assertEquals(Option::Some(10), Enumerable::last($it));

        $this->assertEquals(Option::Some("s"), Enumerable::last("tes"));
        $this->assertEquals(Option::None(), Enumerable::last(""));
    }

    public function testLookup()
    {
        $arr = ['val' => 1, 'val1' => 2];
        $this->assertEquals(Option::Some(2), Enumerable::lookup('val1', $arr));
        $this->assertEquals(Option::Some(1), Enumerable::lookup('val', $arr));
        $this->assertEquals(Option::None(), Enumerable::lookup('none', $arr));
    }
}
