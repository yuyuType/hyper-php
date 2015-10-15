<?php

use Hyper\Data\Option;
use Hyper\Collection;
use Hyper\Func;

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
        $this->assertSame(['val2' => 3, 'val1' => 2, 'val' => 1], iterator_to_array(Collection::reverse($arr)));
        $this->assertSame(['val2' => 3, 'val1' => 2, 'val' => 1], iterator_to_array(Collection::reverse(new ArrayObject($arr))));
        $this->assertSame([], iterator_to_array(Collection::reverse([])));
    }

    public function testIntersperse()
    {
        $arr = ['val' => 1, 'val1' => 2, 'val2' => 3];
        $this->assertSame([1, ',', 2, ',', 3], iterator_to_array(Collection::intersperse(',', $arr)));
        $this->assertSame([], iterator_to_array(Collection::intersperse(',', [])));
        $this->assertsame("t,e,s,t",  join(iterator_to_array(Collection::intersperse(',', "test"))));
        $this->assertSame(["Tom", ",", "Jhon"], iterator_to_array(Collection::intersperse(',', ["Tom", "Jhon"])));
    }

    public function testIntercalate()
    {
        $arr = [[3, 4], [5, 6], [7, 8]];
        $value = Collection::intercalate([1, 2], $arr);
        $this->assertSame([3, 4, 1, 2, 5, 6, 1, 2, 7, 8], iterator_to_array($value));
    }

    public function testTranspose()
    {
        $arr = [[1, 2], [3, 4], [5, 6], [7, 8]];
        $value = Collection::transpose($arr); // [[1, 3, 5, 7], [2, 4, 6, 8]]
        $this->assertSame([[1, 3, 5, 7], [2, 4, 6, 8]], iterator_to_array($value));

        $arr = [[10, 11], [20], [], [30, 31, 32]];
        $value = Collection::transpose($arr);
        $this->assertSame([[10, 20, 30], [11, 31], [32]], iterator_to_array($value));
    }

    public function testFold()
    {
        $value = Collection::fold(function ($z, $x) { return $z + $x; }, 0, range(1, 10));
        $this->assertSame(55, $value);
    }

    public function testFold1()
    {
        $value = Collection::fold1(function ($z, $x) { return $z + $x; }, range(1, 10));
        $this->assertTrue($value->exists(function ($x) { return $x === 55; }));
    }

    public function testConcat()
    {
        $value = Collection::concat([[1, 2, 3], [4, 5, 6]]);
        $this->assertSame([1, 2, 3, 4, 5, 6], iterator_to_array($value));
    }

    public function testConcatMap()
    {
        $value = Collection::concatMap(function ($xs) { return [$xs[0]]; }, [[1, 2, 3], [4, 5, 6]]);
        $this->assertSame([1, 4], iterator_to_array($value));
    }

    public function testAny()
    {
        $this->assertTrue(Collection::any(function ($x) { return $x === 2; }, [1, 2, 3]));
        $this->assertTrue(Collection::any(function ($x) { return $x === 3; }, [1, 2, 3]));
        $this->assertFalse(Collection::any(function ($x) { return $x === 4; }, [1, 2, 3]));
    }

    public function testAll()
    {
        $this->assertTrue(Collection::all(function ($x) { return $x === 2; }, [2, 2, 2]));
        $this->assertTrue(Collection::all(function ($x) { return $x === 3; }, [3, 3, 3]));
        $this->assertFalse(Collection::all(function ($x) { return $x === 4; }, [1, 2, 3]));
        $this->assertFalse(Collection::all(function ($x) { return $x === 2; }, [1, 2, 3]));
    }

    public function testMaximum()
    {
        $this->assertEquals(Option::Some(3), Collection::maximum([1, 2, 3]));
        $this->assertEquals(Option::None(), Collection::maximum([]));
    }

    public function testMaximumBy()
    {
        $this->assertEquals(Option::Some("asdfas"), Collection::maximumBy(function ($x, $y) {
            return mb_strlen($x) > mb_strlen($y);
        }, ["abc", "asdfaf", "sd", "sddf", "asdfas"]));
        $this->assertEquals(Option::None(), Collection::maximumBy(function () {}, []));
    }

    public function testMinimum()
    {
        $this->assertEquals(Option::Some(1), Collection::minimum([1, 2, 3]));
        $this->assertEquals(Option::None(), Collection::minimum([]));
    }

    public function testMinimumBy()
    {
        $this->assertEquals(Option::Some("sd"), Collection::minimumBy(function ($x, $y) {
            return mb_strlen($x) > mb_strlen($y);
        }, ["abc", "asdfaf", "sd", "sddf", "asdfas", "dd"]));
        $this->assertEquals(Option::None(), Collection::minimumBy(function () {}, []));
    }

    public function testSum()
    {
        $this->assertSame(55, Collection::sum(range(1, 10)));
    }

    public function testScan()
    {
        $this->assertSame(
            [1,1,2,6,24,120,720,5040,40320,362880,3628800],
            iterator_to_array(Collection::scan(function ($x, $y) { return $x * $y; }, 1, range(1, 10)))
        );
    }

    public function testScan1()
    {
        $this->assertTrue(Collection::scan1(function ($x, $y) { return $x * $y; }, range(1, 10))
            ->exists(function ($xs) { return [1,2,6,24,120,720,5040,40320,362880,3628800] === iterator_to_array($xs); }));
    }

    public function testMapAccum()
    {
        $value = Collection::mapAccum(function ($x, $y) { return [$x, $x * $y]; }, 1, range(1, 10));
        $this->assertSame([1, [1, 2, 3, 4, 5, 6, 7, 8, 9, 10]], $value);

        $value = Collection::mapAccum(function ($x, $y) { return [$x + $y, $x * $y]; }, 1, range(1, 10));
        $this->assertSame([56, [1, 4, 12, 28, 55, 96, 154, 232, 333, 460]], $value);
    }

    public function testIterate()
    {
        $value = Collection::take(5, Collection::iterate(function ($x) { return $x + $x; }, 1));
        $this->assertSame([1, 2, 4, 8, 16], iterator_to_array($value));
    }

    public function testRepeat()
    {
        $value = Collection::take(5, Collection::repeat(1));
        $this->assertSame([1, 1, 1, 1, 1], iterator_to_array($value));
    }

    public function testReplicate()
    {
        $value = Collection::replicate(5, 1);
        $this->assertSame([1, 1, 1, 1, 1], iterator_to_array($value));
    }

    public function testCycle()
    {
        $value = Collection::take(5, Collection::cycle([1, 2]));
        $this->assertSame([1, 2, 1, 2, 1], iterator_to_array($value));
    }

    public function testUnfold()
    {
        $value = Collection::unfold(function ($x) {
            if ($x > 10) {
                return Option::None();
            }
            return Option::Some([$x, $x * 2]);
        }, 1);
        $this->assertSame([1, 2, 4, 8], iterator_to_array($value));

        $value = Collection::unfold(function ($pair) {
            $a = $pair[0];
            $b = $pair[1];
            return Option::Some([$a + $b, [$b, $b + $a]]);
        }, [0, 1]);
        $this->assertSame([1,2,3,5,8,13,21,34,55,89], iterator_to_array(Collection::take(10, $value)));
    }

    public function testTake()
    {
        $value = Collection::take(5, [1, 2, 3, 4, 5, 6]);
        $this->assertSame([1, 2, 3, 4, 5], iterator_to_array($value));
    }

    public function testDrop()
    {
        $value = Collection::drop(3, [1, 2, 3, 4, 5]);
        $this->assertSame([4, 5], iterator_to_array($value));
    }

    public function testTakeWhile()
    {
        $value = Collection::takeWhile(function ($x) { return $x < 3; }, [1,2,3,4,5,1,2,3]);
        $this->assertSame([1, 2], iterator_to_array($value));

        $value = Collection::takeWhile(function ($x) { return $x < 9; }, [1,2,3]);
        $this->assertSame([1,2,3], iterator_to_array($value));

        $value = Collection::takeWhile(function ($x) { return $x < 0; }, [1,2,3]);
        $this->assertSame([], iterator_to_array($value));
    }

    public function testDropWhile()
    {
        $value = Collection::dropWhile(function ($x) { return $x < 3; }, [1,2,3,4,5,1,2,3]);
        $this->assertSame([2 => 3, 3 => 4, 4 => 5, 5 => 1, 6 => 2, 7 => 3], iterator_to_array($value));

        $value = Collection::dropWhile(function ($x) { return $x < 9; }, [1,2,3]);
        $this->assertSame([], iterator_to_array($value));

        $value = Collection::dropWhile(function ($x) { return $x < 0; }, [1,2,3]);
        $this->assertSame([1,2,3], iterator_to_array($value));
    }

    public function testSpan()
    {
        $value = iterator_to_array(Collection::span(function ($x) { return $x < 3; }, [1,2,3,4,5,1,2,3]));
        $this->assertSame([0 => 1, 1 => 2], iterator_to_array($value[0]));
        $this->assertSame([2 => 3, 3 => 4, 4 => 5, 5 => 1, 6 => 2, 7 => 3], iterator_to_array($value[1]));
    }

    public function testGroup()
    {
        $group = Collection::group('Mississippi');
        $this->assertSame(array('M'), iterator_to_array($group->current()));
        $group->next();
        $this->assertSame(array('i'), iterator_to_array($group->current()));
        $group->next();
        $this->assertSame(array('s', 's'), iterator_to_array($group->current()));
        $group->next();
        $this->assertSame(array('i'), iterator_to_array($group->current()));
        $group->next();
        $this->assertSame(array('s', 's'), iterator_to_array($group->current()));
        $group->next();
        $this->assertSame(array('i'), iterator_to_array($group->current()));
        $group->next();
        $this->assertSame(array('p', 'p'), iterator_to_array($group->current()));
        $group->next();
        $this->assertSame(array('i'), iterator_to_array($group->current()));
        $group->next();
        $this->assertFalse($group->valid());
    }

    public function testLookup()
    {
        $arr = ['val' => 1, 'val1' => 2];
        $this->assertEquals(Option::Some(2), Collection::lookup('val1', $arr));
        $this->assertEquals(Option::Some(1), Collection::lookup('val', $arr));
        $this->assertEquals(Option::None(), Collection::lookup('none', $arr));
    }

    public function testFind()
    {
        $arr = ['val' => 1, 'val1' => 2];
        $this->assertEquals(Option::Some(1), Collection::find(Func::bind('Hyper\Func::equal', 1), $arr));
        $this->assertEquals(Option::Some(2), Collection::find(Func::bind('Hyper\Func::equal', 2), $arr));
        $this->assertEquals(Option::None(), Collection::find(Func::bind('Hyper\Func::equal', 3), $arr));
    }

    public function testFilter()
	{
		$even = function ($x) {
			return $x % 2 === 0;
		};
		$this->assertEquals(
			array(array(1, 3, 5, 7, 9), array(2, 4, 6, 8, 10)),
			Collection::toArray(Collection::partition(Collection::negate($even), range(1, 10)))
		);
    }

    public function testZip()
    {
        $xs = range(0, 5);
        $ys = range(6, 9);
        $this->assertSame(
            array(
                array(0, 6),
                array(1, 7),
                array(2, 8),
                array(3, 9),
            ),
            iterator_to_array(Collection::zip($xs, $ys))
        );
    }

    public function testZip3()
    {
        $xs = range(0, 5);
        $ys = range(6, 9);
        $zs = range(10, 15);
        $this->assertSame(
            array(
                array(0, 6, 10),
                array(1, 7, 11),
                array(2, 8, 12),
                array(3, 9, 13),
            ),
            iterator_to_array(Collection::zip3($xs, $ys, $zs))
        );
    }

    public function testZipWith()
    {
        $xs = range(0, 5);
        $ys = range(6, 9);
        $this->assertSame(
            array(
                0,
                7,
                16,
                27,
            ),
            iterator_to_array(Collection::zipWith(function ($a, $b) { return $a * $b; }, $xs, $ys))
        );
    }

    public function testUnzip()
    {
        $xs = range(0, 5);
        $ys = range(6, 9);
        $ziped = iterator_to_array(Collection::zip($xs, $ys));
        $this->assertSame(
            array(
                array(0, 1, 2, 3),
                array(6, 7, 8, 9),
            ),
            Collection::toArray(Collection::unzip($ziped))
        );

        $xs = range(0, 5);
        $ys = range(6, 9);
        $ziped = iterator_to_array(Collection::zip($xs, $ys));
        $this->assertSame(
            array(0, 1, 2, 3),
            iterator_to_array(Collection::unzip($ziped)[0])
        );
        $this->assertSame(
            array(6, 7, 8, 9),
            iterator_to_array(Collection::unzip($ziped)[1])
        );
    }

    public function testSort()
    {
        $xs = range(0, 5);
        $ys = array_reverse($xs);
        $sorted = Collection::sort($ys);
        $this->assertSame($xs, $sorted);
    }
}
