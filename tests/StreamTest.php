<?php

use Hyper\Stream;

class StreamTest extends PHPUnit_Framework_TestCase
{
    public function testHead()
    {
        $value = (new Stream(range(1, 10)))
            ->head()
            ->get();
        $this->assertTrue($value->exists(function ($x) { return $x === 1; }));

        $value = (new Stream([]))
            ->head()
            ->get();
        $this->assertFalse($value->exists(function ($x) { return $x === 1; }));

        $value = (new Stream(new ArrayIterator([1, 2, 3, 4, 5, 6, 7, 8, 9, 10])))
            ->head()
            ->get();
        $this->assertTrue($value->exists(function ($x) { return $x === 1; }));

        $value = (new Stream("test"))
            ->head()
            ->get();
        $this->assertTrue($value->exists(function ($x) { return $x === "t"; }));

        $value = (new Stream(""))
            ->head()
            ->get();
        $this->assertFalse($value->exists(function ($x) { return $x === "t"; }));
    }

    public function testLast()
    {
        $value = (new Stream(range(1, 10)))
            ->last()
            ->get();
        $this->assertTrue($value->exists(function ($x) { return $x === 10; }));

        $value = (new Stream(new ArrayIterator([1, 2, 3, 4, 5, 6, 7, 8, 9, 10])))
            ->last()
            ->get();
        $this->assertTrue($value->exists(function ($x) { return $x === 10; }));

        $value = (new Stream([]))
            ->last()
            ->get();
        $this->assertFalse($value->exists(function ($x) { return $x === 10; }));

        $value = (new Stream("testes"))
            ->last()
            ->get();
        $this->assertTrue($value->exists(function ($x) { return $x === "s"; }));

        $value = (new Stream(""))
            ->last()
            ->get();
        $this->assertFalse($value->exists(function ($x) { return $x === "t"; }));
    }

    public function testFilter()
    {
        $value = (new Stream(range(1, 10)))
            ->filter(function ($x) { return $x % 2 === 0; })
            ->get();
        $this->assertSame([2, 4, 6, 8, 10], iterator_to_array($value));

        $value = (new Stream(new ArrayIterator([1, 2, 3, 4, 5, 6, 7, 8, 9, 10])))
            ->filter(function ($x) { return $x % 2 === 0; })
            ->get();
        $this->assertSame([2, 4, 6, 8, 10], iterator_to_array($value));

        $value = (new Stream("test"))
            ->filter(function ($c) { return $c === "t"; })
            ->get();
        $this->assertSame("tt", join(iterator_to_array($value)));
    }

    public function testMap()
    {
        $value = (new Stream([1, 2, 3]))
            ->map(function ($x) { return $x * $x; })
            ->get();
        $this->assertSame([1, 4, 9], iterator_to_array($value));

        $value = (new Stream(new ArrayIterator([1, 2, 3])))
            ->map(function ($x) { return $x * $x; })
            ->toArray();
        $this->assertSame([1, 4, 9], $value);

        $value = (new Stream("test"))
            ->map("strtoupper")
            ->toString();
        $this->assertSame("TEST", $value);
    }

    public function testBind()
    {
        $stream = new Stream(range(1, 5));
        $result = $stream->bind('array_map', function ($x) { return $x * 2; })
            ->get();
        $this->assertSame([2, 4, 6, 8, 10], $result);
    }
}
