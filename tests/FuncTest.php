<?php

use Hyper\Func;

class FuncTestFoo
{
    public static function test()
    {
        return 99;
    }

    public function bar()
    {
        return 'hogehoge';
    }

    public function hoge($a, $b, $c, $d)
    {
        return $a.$b.$c.$d;
    }
}

function functionNoParams()
{
    return 'Hellow';
}

function functionTest($x, $y, $z)
{
    return $x * $y * $z;
}

function plus($x, $y)
{
    return $x + $y;
}

function multiply($x, $y)
{
    return $x * $y;
}

class FuncTest extends PHPUnit_Framework_TestCase
{
    public function testIdentity()
    {
        $this->assertSame(1, Func::identity(1));
        $this->assertSame(range(0, 10), Func::identity([0, 1, 2, 3, 4, 5, 6, 7, 8, 9, 10]));
    }

    public function testEvaluate()
    {
        $this->assertSame(6, Func::evaluate(Func::bind('plus', 3, 3)));
        $this->assertSame(6, Func::evaluate(6));
    }

    public function testBind()
    {
        $bind = Func::bind('plus', 3, 3);
        $this->assertTrue(is_callable($bind));
        $this->assertSame(6, call_user_func($bind));

        $bind = Func::bind('plus', 3);
        $this->assertTrue(is_callable($bind));
        $this->assertSame(9, call_user_func($bind, 6));

        $bind = Func::bind('plus');
        $this->assertTrue(is_callable($bind));
        $this->assertSame(9, call_user_func($bind, 4, 5));

        $bind = Func::bind(function () { return null; });
        $this->assertTrue(is_callable($bind));
        $this->assertNull(call_user_func($bind));

        $bind = Func::bind([new FuncTestFoo, 'bar']);
        $this->assertTrue(is_callable($bind));
        $this->assertSame('hogehoge', call_user_func($bind));

        $bind = Func::bind(['FuncTestFoo', 'bar']);
        $this->assertTrue(is_callable($bind));
        $this->assertSame('hogehoge', call_user_func($bind));
    }

    public function testCompose()
    {
        $this->assertSame(
            17,
            call_user_func(
                Func::compose(
                    Func::bind('plus', 2),
                    Func::bind('multiply', 3)
                ),
                5
            )
        );
    }

    public function testFlip()
    {
        $div = function ($x, $y) {
            return $x / $y;
        };
        $this->assertSame(
            call_user_func($div, 5, 3),
            call_user_func(Func::flip($div), 3, 5)
        );
    }

    public function testCurry()
    {
        // Clojuer Test
        $curried = Func::curry(function ($x, $y, $z) { return $x + $y + $z; });
        $applyOne = $curried(1);
        $applyTow = $applyOne(2);
        $this->assertSame(6, $applyTow(3)); // 1 + 2 + 3 = 6

        $curried = Func::curry(function () { return 100; });
        $this->assertSame(100, $curried());

        // Static Class Method Test
        $curried = Func::curry('plus');
        $applyOne = $curried(1);
        $this->assertSame(3, $applyOne(2)); // 1 + 2 = 3

        $curried = Func::curry('plus');
        $applyOne = $curried(5);
        $this->assertSame(10, $applyOne(5)); // 5 + 5 = 3

        $curried = Func::curry('FuncTestFoo::test');
        $this->assertSame(99, $curried());

        // Class Method Test
        $curried = Func::curry([new FuncTestFoo, 'hoge']);
        $curried = $curried('Hellow');
        $curried = $curried(',');
        $curried = $curried(' ');
        $this->assertSame('Hellow, World', $curried('World'));

        $curried = Func::curry([new FuncTestFoo, 'bar']);
        $this->assertSame('hogehoge', $curried());

        // Function Test
        $curried = Func::curry('functionTest');
        $curried = $curried(3);
        $curried = $curried(5);
        $this->assertSame(30, $curried(2));

        $curried = Func::curry('\functionTest');
        $curried = $curried(8);
        $curried = $curried(7);
        $this->assertSame(0, $curried(0));

        $curried = Func::curry('functionNoParams');
        $this->assertSame('Hellow', $curried());
    }

    public function testPlus()
    {
        $this->assertSame(3, plus(1, 2));
    }

}

