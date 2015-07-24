<?php

use Hyper\Data\Option;
use Hyper\Func;

class OptionTest extends PHPUnit_Framework_TestCase
{
    public function testOption()
    {
        $this->assertEquals(Option::Some(1), Option::OK(1));

        $this->assertEquals(Option::None(), Option::NG());
    }

    public function testIsEmpty()
    {
        $this->assertFalse(Option::Some(1)->isEmpty);

        $this->assertTrue(Option::None()->isEmpty);
    }

    public function testGetOrElse()
    {
        $this->assertSame(1, Option::Some(1)->getOrElse(0));
        $this->assertSame(1, Option::OK(1)->getOrElse(0));
        $this->assertSame(
            'HELLOW WORLD',
            Option::Some('Hellow World')->map('strtoupper')->getOrElse('')
        );

        $this->assertSame(0, Option::None(1)->getOrElse(0));
        $this->assertSame(10, Option::None(1)->getOrElse(function () { return 10; }));
        $this->assertSame(0, Option::NG(1)->getOrElse(0));
        $this->assertSame(
            '',
            Option::None()->map('strtoupper')->getOrElse('')
        );
    }

    public function testOrElse()
    {
        $this->assertEquals(Option::Some('hoge'), Option::Some('hoge')->orElse(Option::Some('hogehoge')));
        $this->assertEquals(Option::Some('hoge'), Option::Some('hoge')->orElse(Option::None()));

        $this->assertEquals(Option::Some('hogehoge'), Option::None()->orElse(Option::Some('hogehoge')));
        $this->assertEquals(Option::Some('hogehoge'), Option::None()->orElse(function () { return Option::Some('hogehoge'); }));
        $this->assertSame(Option::None(), Option::None()->orElse(Option::None()));
    }

    public function testForEach()
    {
        Option::Some('hoge')->each(function ($x) {
            $this->assertSame('hoge', $x);
        });

        Option::None()->each(function ($_) {
            // Noneの場合ココが呼ばれてはけないので呼ばれた場合は必ず失敗するようにしている
            $this->assertTrue(false);
        });
    }

    public function testMap()
    {
        $this->assertEquals(Option::Some(2), Option::Some(1)->map('multiply', 2));
        $this->assertSame(2, Option::Some(1)->map('multiply', 2)->getOrElse(0));

        $this->assertSame(Option::None(), Option::None(1)->map('multiply', 2));
        $this->assertSame(0, Option::None(1)->map('multiply', 2)->getOrElse(0));
    }

    public function testFold()
    {
        $this->assertSame(
            Option::Some(2)->map(function ($x) { return $x * 3; })->getOrElse(0),
            Option::Some(2)->fold(0, function ($x) { return $x * 3; })
        );

        $this->assertSame(
            Option::None()->map(function ($x) { return $x * 3; })->getOrElse(0),
            Option::None()->fold(0, function ($x) { return $x * 3; })
        );

        $this->assertSame(
            6,
            Option::Some(2)->fold(0, function ($x) { return $x * 3; })
        );
        $this->assertSame(
            6,
            Option::Some(2)->fold(function () { return 99; }, function ($x) { return $x * 3; })
        );

        $this->assertSame(
            99,
            Option::None()->fold(function () { return 99; }, function ($x) { return $x * 3; })
        );
    }

    public function testFlatten()
    {
        try {
            Option::Some('test')->flatten();
        } catch (LogicException $ex) {
            $this->assertInstanceOf('LogicException', $ex);
        }

        $this->assertEquals(Option::Some('test'), Option::Some(Option::Some('test'))->flatten());
        $this->assertSame(Option::None(), Option::Some(Option::None())->flatten());

        $this->assertSame(Option::None(), Option::None()->flatten());
    }

    public function testFlatMap()
    {
        $this->assertEquals(Option::Some('TEST'), Option::Some('test')->flatMap(Func::compose('Hyper\Data\Option::Some', 'strtoupper')));

        $this->assertSame(Option::None(), Option::None()->flatMap(Func::compose('Hyper\Data\Option::Some', 'strtoupper')));
        $this->assertSame(Option::None(), Option::Some(Option::None())->flatMap(function ($x) { return $x; }));

        $optPattern = Option::Some('/(\w+) (\d+), (\d+)/i');
        $optReplacement = Option::Some('${1}1,$3');
        $optString = Option::Some('April 15, 2003');
        $this->assertEquals(
            Option::Some('April1,2003'),
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
        $this->assertEquals(Option::Some(14), Option::Some(5)->map(Func::curry('\plus'))->ap(Option::Some(9)));

        $this->assertSame(Option::None(), Option::Some(5)->map(Func::curry('\plus'))->ap(Option::None()));
        $this->assertSame(Option::None(), Option::None()->map(Func::curry('\plus'))->ap(Option::Some(9)));

        $optPattern = Option::Some('/(\w+) (\d+), (\d+)/i');
        $optReplacement = Option::Some('${1}1,$3');
        $optString = Option::Some('April 15, 2003');
        $this->assertEquals(
            Option::Some('April1,2003'),
            $optPattern->map(Func::curry('preg_replace'))->ap($optReplacement)->ap($optString)
        );
        $this->assertSame(
            Option::None(),
            Option::None()->map(Func::curry('preg_replace'))->ap($optReplacement)->ap($optString)
        );
        $this->assertSame(
            Option::None(),
            $optPattern->map(Func::curry('preg_replace'))->ap(Option::None())->ap($optString)
        );
        $this->assertSame(
            Option::None(),
            $optPattern->map(Func::curry('preg_replace'))->ap($optReplacement)->ap(Option::None())
        );
        $this->assertSame(
            Option::None(),
            Option::None()->map(Func::curry('preg_replace'))->ap($optReplacement)->ap(Option::None())
        );
        $this->assertSame(
            Option::None(),
            Option::None()->map(Func::curry('preg_replace'))->ap(Option::None())->ap(Option::None())
        );
    }

    public function testMatch()
    {
        try {
            Option::Some(1)->match();
        } catch (LogicException $ex) {
            $this->assertInstanceOf('LogicException', $ex);
            $this->assertSame("You must set caseSome parameter.", $ex->getMessage());
        }
        try {
            Option::Some(1)->match(Option::caseNone(0));
        } catch (LogicException $ex) {
            $this->assertInstanceOf('LogicException', $ex);
            $this->assertSame("You must set caseSome parameter.", $ex->getMessage());
        }
        $this->assertSame([3], Option::Some(1)->match(
            Option::caseSome([3]),
            Option::caseNone([])
        ));
        $this->assertSame([1], Option::Some(2)->match(
            Option::caseNone([]),
            Option::caseSome(function ($_) { return [1]; })
        ));
        $this->assertSame([5], Option::Some(5)->match(
            Option::caseNone([]),
            Option::caseSome(function ($x) { return [$x]; })
        ));
        $this->assertSame(3, Option::Some(5)->match(
            Option::caseSome(Func::bind('plus', 1, 2)),
            Option::caseNone(0)
        ));
        $this->assertSame(7, Option::Some(5)->match(
            Option::caseNone(0),
            Option::caseSome(Func::bind('plus', 2))
        ));

        try {
            Option::None()->match();
        } catch (LogicException $ex) {
            $this->assertInstanceOf('LogicException', $ex);
            $this->assertSame("You must set caseNone parameter.", $ex->getMessage());
        }
        try {
            Option::None()->match(Option::caseNone(0));
        } catch (LogicException $ex) {
            $this->assertInstanceOf('LogicException', $ex);
            $this->assertSame("You must set caseNone parameter.", $ex->getMessage());
        }
        $this->assertSame([], Option::None()->match(
            Option::caseOK([3]),
            Option::caseNG([])
        ));
        $this->assertSame([], Option::None()->match(
            Option::caseNone([]),
            Option::caseSome(function ($_) { return [1]; })
        ));
        $this->assertSame([99], Option::None()->match(
            Option::caseNone([99]),
            Option::caseSome(function ($x) { return [$x]; })
        ));
        $this->assertSame(0, Option::None()->match(
            Option::caseSome(Func::bind('plus', 1, 2)),
            Option::caseNone(0)
        ));
        $this->assertSame(9, Option::None()->match(
            Option::caseNone(9),
            Option::caseSome(Func::bind('plus', 2))
        ));
    }

}

