<?php

use Hyper\Data\Either;
use Hyper\Func;

class EitherTest extends PHPUnit_Framework_TestCase
{
    public function ret0($_)
    {
        return 0;
    }

    public function testEither()
    {
        $this->assertEquals(Either::Right(1), Either::Success(1));

        $this->assertEquals(Either::Left('fail'), Either::Failure('fail'));
    }

    public function testIsLeft()
    {
        $this->assertFalse(Either::Right(1)->isLeft);

        $this->assertTrue(Either::Left('fail')->isLeft);
    }

    public function testIsRight()
    {
        $this->assertTrue(Either::Right(1)->isRight);

        $this->assertFalse(Either::Left('fail')->isRight);
    }

    public function testGetOrElse()
    {
        $this->assertSame(1, Either::Right(1)->getOrElse([$this, 'ret0']));
        $this->assertSame(1, Either::Success(1)->getOrElse([$this, 'ret0']));
        $this->assertSame(
            'HELLOW WORLD',
            Either::Right('Hellow World')->map('strtoupper')->getOrElse('Hyper\Func::identity')
        );

        $this->assertSame(1, Either::Left(1)->getOrElse('Hyper\Func::identity'));
        $this->assertSame(10, Either::Left(1)->getOrElse(function ($_) { return 10; }));
        $this->assertSame(0, Either::Failure(1)->getOrElse(function ($_) { return 0; }));
        $this->assertSame(999, Either::Failure(1)->getOrElse(Func::bind('Hyper\Func::identity', 999)));
        $this->assertSame(
            'fail',
            Either::Left('fail')->map('strtoupper')->getOrElse('Hyper\Func::identity')
        );
    }

    public function testOrElse()
    {
        $this->assertEquals(Either::Right('hoge'), Either::Right('hoge')->orElse(Either::Right('hogehoge')));
        $this->assertEquals(Either::Right('hoge'), Either::Right('hoge')->orElse(Either::Left('fail')));

        $this->assertEquals(Either::Right('hogehoge'), Either::Left('fail')->orElse(Either::Right('hogehoge')));
        $this->assertEquals(Either::Right('hogehoge'), Either::Left('fail')->orElse(function () { return Either::Right('hogehoge'); }));
        $this->assertEquals(Either::Left('fail'), Either::Left('fail')->orElse(Either::Left('fail')));
    }

    public function testForEach()
    {
        Either::Right('hoge')->each(function ($x) {
            $this->assertSame('hoge', $x);
        });

        Either::Left('fail')->each(function ($_) {
            // Leftの場合ココが呼ばれてはけないので呼ばれた場合は必ず失敗するようにしている
            $this->assertTrue(false);
        });
    }

    public function testMap()
    {
        $this->assertEquals(Either::Right(6), Either::Right(3)->map('multiply', 2));
        $this->assertSame(2, Either::Right(1)->map('multiply', 2)->getOrElse(function ($_) { return 0; }));

        $this->assertEquals(Either::Left('fail'), Either::Left('fail')->map('multiply', 2));
        $this->assertSame(0, Either::Left(1)->map('multiply', 2)->getOrElse(function ($_) { return 0; }));
    }

    public function testFold()
    {
        $this->assertSame(
            Either::Right(2)->map(function ($x) { return $x * 3; })->getOrElse([$this, 'ret0']),
            Either::Right(2)->fold([$this, 'ret0'], function ($x) { return $x * 3; })
        );

        $this->assertSame(
            Either::Left('fail')->map(function ($x) { return $x * 3; })->getOrElse([$this, 'ret0']),
            Either::Left('fail')->fold([$this, 'ret0'], function ($x) { return $x * 3; })
        );

        $this->assertSame(
            6,
            Either::Right(2)->fold([$this, 'ret0'], function ($x) { return $x * 3; })
        );
        $this->assertSame(
            6,
            Either::Right(2)->fold(function ($_) { return 99; }, function ($x) { return $x * 3; })
        );

        $this->assertSame(
            99,
            Either::Left('fail')->fold(function ($_) { return 99; }, function ($x) { return $x * 3; })
        );
    }

    public function testFlatten()
    {
        try {
            Either::Right('test')->flatten();
        } catch (LogicException $ex) {
            $this->assertInstanceOf('LogicException', $ex);
        }

        $this->assertEquals(Either::Right('test'), Either::Right(Either::Right('test'))->flatten());
        $this->assertEquals(Either::Left('fail'), Either::Right(Either::Left('fail'))->flatten());

        $this->assertEquals(Either::Left('fail'), Either::Left('fail')->flatten());
    }

    public function testFlatMap()
    {
        $this->assertEquals(Either::Right('TEST'), Either::Right('test')->flatMap(Func::compose('Hyper\Data\Either::Right', 'strtoupper')));

        $this->assertEquals(Either::Left('fail'), Either::Left('fail')->flatMap(Func::compose('Hyper\Data\Either::Right', 'strtoupper')));
        $this->assertEquals(Either::Left('fail'), Either::Right(Either::Left('fail'))->flatMap(function ($x) { return $x; }));

        $optPattern = Either::Right('/(\w+) (\d+), (\d+)/i');
        $optReplacement = Either::Right('${1}1,$3');
        $optString = Either::Right('April 15, 2003');
        $this->assertEquals(
            Either::Right('April1,2003'),
            $optPattern->flatMap(function ($pattern) use($optReplacement, $optString) {
                return $optReplacement->flatMap(function ($replacement) use($pattern, $optString) {
                    return $optString->map(function ($string) use($pattern, $replacement) {
                        return preg_replace($pattern, $replacement, $string);
                    });
                });
            })
        );
    }

    public function testApplicate()
    {
        $this->assertEquals(Either::Right(14), Either::Right(5)->map(Func::curry('\plus'))->ap(Either::Right(9)));

        $this->assertEquals(Either::Left('fail'), Either::Right(5)->map(Func::curry('\plus'))->ap(Either::Left('fail')));
        $this->assertEquals(Either::Left('fail'), Either::Left('fail')->map(Func::curry('\plus'))->ap(Either::Right(9)));

        $optPattern = Either::Right('/(\w+) (\d+), (\d+)/i');
        $optReplacement = Either::Right('${1}1,$3');
        $optString = Either::Right('April 15, 2003');
        $this->assertEquals(
            Either::Right('April1,2003'),
            $optPattern->map(Func::curry('preg_replace'))->ap($optReplacement)->ap($optString)
        );
        $this->assertEquals(
            Either::Left('fail'),
            Either::Left('fail')->map(Func::curry('preg_replace'))->ap($optReplacement)->ap($optString)
        );
        $this->assertEquals(
            Either::Left('fail'),
            $optPattern->map(Func::curry('preg_replace'))->ap(Either::Left('fail'))->ap($optString)
        );
        $this->assertEquals(
            Either::Left('fail'),
            $optPattern->map(Func::curry('preg_replace'))->ap($optReplacement)->ap(Either::Left('fail'))
        );
        $this->assertEquals(
            Either::Left('fail'),
            Either::Left('fail')->map(Func::curry('preg_replace'))->ap($optReplacement)->ap(Either::Left('fail'))
        );
        $this->assertEquals(
            Either::Left('fail'),
            Either::Left('fail')->map(Func::curry('preg_replace'))->ap(Either::Left('fail'))->ap(Either::Left('fail'))
        );
    }

    public function testMatch()
    {
        try {
            Either::Right(1)->match();
        } catch (LogicException $ex) {
            $this->assertInstanceOf('LogicException', $ex);
            $this->assertSame("You must set caseRight parameter.", $ex->getMessage());
        }
        try {
            Either::Right(1)->match(Either::caseLeft(0));
        } catch (LogicException $ex) {
            $this->assertInstanceOf('LogicException', $ex);
            $this->assertSame("You must set caseRight parameter.", $ex->getMessage());
        }
        $this->assertSame([3], Either::Right(1)->match(
            Either::caseRight([3]),
            Either::caseLeft([])
        ));
        $this->assertSame([1], Either::Right(2)->match(
            Either::caseLeft([]),
            Either::caseRight(function ($_) { return [1]; })
        ));
        $this->assertSame([5], Either::Right(5)->match(
            Either::caseLeft([]),
            Either::caseRight(function ($x) { return [$x]; })
        ));
        $this->assertSame(3, Either::Right(5)->match(
            Either::caseRight(Func::bind('plus', 1, 2)),
            Either::caseLeft(0)
        ));
        $this->assertSame(7, Either::Right(5)->match(
            Either::caseLeft(0),
            Either::caseRight(Func::bind('plus', 2))
        ));

        try {
            Either::Left('fail')->match();
        } catch (LogicException $ex) {
            $this->assertInstanceOf('LogicException', $ex);
            $this->assertSame("You must set caseLeft parameter.", $ex->getMessage());
        }
        try {
            Either::Left('fail')->match(Either::caseLeft(0));
        } catch (LogicException $ex) {
            $this->assertInstanceOf('LogicException', $ex);
            $this->assertSame("You must set caseLeft parameter.", $ex->getMessage());
        }
        $this->assertSame([], Either::Left('fail')->match(
            Either::caseSuccess([3]),
            Either::caseFailure([])
        ));
        $this->assertSame([], Either::Left('fail')->match(
            Either::caseLeft([]),
            Either::caseRight(function ($_) { return [1]; })
        ));
        $this->assertSame(4, Either::Left('fail')->match(
            Either::caseLeft(function ($x) { return mb_strlen($x); }),
            Either::caseRight(function ($x) { return [$x]; })
        ));
        $this->assertSame(99, Either::Left('fail')->match(
            Either::caseRight(Func::bind('plus', 1, 2)),
            Either::caseLeft(Func::bind('Hyper\Func::identity', 99))
        ));
        $this->assertSame(0, Either::Left('fail')->match(
            Either::caseLeft([$this, 'ret0']),
            Either::caseRight(Func::bind('plus', 2))
        ));
    }

    public function testExists()
    {
        $this->assertTrue(Either::Right(1)->exists(function ($x) { return $x === 1; }));
        $this->assertTrue(Either::Left("test")->exists(function ($x) { return $x === "test"; }));
        $this->assertFalse(Either::Right(10)->exists(function ($x) { return $x === 1; }));
        $this->assertFalse(Either::Left("test")->exists(function ($x) { return $x === "tes"; }));
    }
}
