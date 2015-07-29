<?php

use Hyper\Data\Option;
use Hyper\Collection;

class CollectionTest extends PHPUnit_Framework_TestCase
{
    public function testHead()
    {
        $arr = ['val' => 1, 'val1' => 2];
        $this->assertEquals(Option::Some(1), Collection::head($arr));
        $this->assertEquals(Option::None(), Collection::head([]));

        $it = new ArrayIterator(range(1, 10));
        $this->assertEquals(Option::Some(1), Collection::head($it));
        $this->assertEquals(Option::Some(1), Collection::head($it));

        $this->assertEquals(Option::Some("t"), Collection::head("test"));
        $this->assertEquals(Option::None(), Collection::head(""));
    }

    public function testLast()
    {
        $arr = ['val' => 1, 'val1' => 2];
        $this->assertEquals(Option::Some(2), Collection::last($arr));
        $this->assertEquals(Option::None(), Collection::last([]));

        $it = new ArrayIterator(range(1, 10));
        $this->assertEquals(Option::Some(10), Collection::last($it));
        $this->assertEquals(Option::Some(10), Collection::last($it));

        $this->assertEquals(Option::Some("s"), Collection::last("tes"));
        $this->assertEquals(Option::None(), Collection::last(""));
    }

    public function testTail()
    {
        $arr = ['val' => 1, 'val1' => 2, 'val2' => 3];
        $this->assertSame(['val1' => 2, 'val2' => 3], iterator_to_array(Collection::tail($arr)));
    }

    public function testInit()
    {
        $arr = ['val' => 1, 'val1' => 2, 'val2' => 3];
        $this->assertSame(['val' => 1, 'val1' => 2], iterator_to_array(Collection::init($arr)));
    }

    public function testUncons()
    {
        $arr = ['val' => 1, 'val1' => 2, 'val2' => 3];
        $this->assertTrue(Collection::uncons($arr)->exists(function ($xs) {
            return 1 === $xs[0] && ['val1' => 2, 'val2' => 3] === iterator_to_array($xs[1]);
        }));
    }

    public function testIsEmpty()
    {
        $arr = ['val' => 1, 'val1' => 2, 'val2' => 3];
        $this->assertFalse(Collection::isEmpty($arr));
        $this->assertTrue(Collection::isEmpty([]));
    }

    public function testCount()
    {
        $arr = ['val' => 1, 'val1' => 2, 'val2' => 3];
        $this->assertSame(3, Collection::count($arr));
        $this->assertSame(0, Collection::count([]));
    }

    public function testReverse()
    {
        $arr = ['val' => 1, 'val1' => 2, 'val2' => 3];
        $this->assertSame(['val2' => 3, 'val1' => 2, 'val' => 1], Collection::reverse($arr));
        $this->assertSame(['val2' => 3, 'val1' => 2, 'val' => 1], Collection::reverse(new ArrayObject($arr)));
        $this->assertSame([], Collection::reverse([]));
    }

    public function testIntersperse()
    {
        $arr = ['val' => 1, 'val1' => 2, 'val2' => 3];
        $this->assertSame([1, ',', 2, ',', 3], iterator_to_array(Collection::intersperse(',', $arr)));
        $this->assertSame([], iterator_to_array(Collection::intersperse(',', [])));
        $this->assertsame("t,e,s,t",  join(iterator_to_array(Collection::intersperse(',', "test"))));
        $this->assertSame(["Tom", ",", "Jhon"], iterator_to_array(Collection::intersperse(',', ["Tom", "Jhon"])));
    }

    public function testLookup()
    {
        $arr = ['val' => 1, 'val1' => 2];
        $this->assertEquals(Option::Some(2), Collection::lookup('val1', $arr));
        $this->assertEquals(Option::Some(1), Collection::lookup('val', $arr));
        $this->assertEquals(Option::None(), Collection::lookup('none', $arr));
    }
}
