<?php
/**
 * arrayに対する操作関数群
 *
 * @package Hyper
 * @author yuyuType
 * @link https://github.com/yuyuType/hyper-php
 */

namespace Hyper;

use Hyper\Data\Collections\FilterdIterator;
use Hyper\Data\Collections\InitIterator;
use Hyper\Data\Collections\MapIterator;
use Hyper\Data\Collections\StringIterator;
use Hyper\Data\Option;

/**
 * arrayに対する操作関数群
 *
 * @author yuyuType
 */
class Collection
{
    /**
     * toIterator
     *
     * @param mixed $iterator
     * @return mixed
     * @throws \LogicException
     */
    public static function toIterator($iterator)
    {
        if ($iterator instanceof \IteratorAggregate) {
            return $iterator->getIterator();
        } elseif ($iterator instanceof \Iterator) {
            return $iterator;
        } elseif (is_array($iterator)) {
            return new \ArrayIterator($iterator);
        } elseif (is_string($iterator)) {
            return (new StringIterator($iterator))->getIterator();
        }
        throw new \LogicException("Cannot comvert to Iterator.");
    }

    // Base functions

    /**
     * head
     *
     * @param array|Traversable $iterator
     * @return None|Some
     */
    public static function head($iterator)
    {
        foreach (self::toIterator($iterator) as $value) {
            return Option::Some($value);
        }
        return Option::None();
    }

    /**
     * last
     *
     * @param array|Traversable $iterator
     * @return None|Some
     */
    public static function last($iterator)
    {
        $isEmpty = true;
        foreach (self::toIterator($iterator) as $value) {
            $isEmpty = false;
        }
        return $isEmpty
            ? Option::None()
            : Option::Some($value);
    }

    /**
     * tail
     *
     * @param array|Traversable $iterator
     * @return Traversable
     * @SuppressWarnings(PHPMD.ShortVariable)
     */
    public static function tail($iterator)
    {
        $it = self::toIterator($iterator);
        return $it->valid()
            ? new \LimitIterator(self::toIterator($iterator), 1)
            : new \EmptyIterator();
    }

    /**
     * init
     *
     * @param array|Traversable $iterator
     * @return Traversable
     * @SuppressWarnings(PHPMD.ShortVariable)
     */
    public static function init($iterator)
    {
        $it = self::toIterator($iterator);
        return $it->valid()
            ? new InitIterator(self::toIterator($iterator))
            : new \EmptyIterator();
    }

    /**
     * uncons
     *
     * @param array|Traversable $iterator
     * @return None|Some
     * @SuppressWarnings(PHPMD.ShortVariable)
     */
    public static function uncons($iterator)
    {
        $it = self::toIterator($iterator);
        return $it->valid()
            ? self::head($it)->map(function ($x) use ($it) { return [$x, self::tail($it)]; })
            : Option::None();
    }

    /**
     * isEmpty
     *
     * @param array|Traversable $iterator
     * @return boolean
     * @SuppressWarnings(PHPMD.ShortVariable)
     */
    public static function isEmpty($iterator)
    {
        $it = self::toIterator($iterator);
        return !$it->valid();
    }

    /**
     * count
     *
     * @param array|Traversable $iterator
     * @return integer
     * @SuppressWarnings(PHPMD.UnusedLocalVariable)
     */
    public static function count($iterator)
    {
        $count = 0;
        foreach (self::toIterator($iterator) as $value) {
            ++$count;
        }
        return $count;
    }

    // Transformations

    /**
     * map
     *
     * ex)
     *
     * ``` php
     * $arr = [1, 2, 3];
     * $value = Collection::map(function ($x) { return $x * $x; }, $arr); // [1, 4, 9]
     * ```
     *
     * @param callable $f
     * @param mixed $iterator
     * @return MapIterator
     * @SuppressWarnings(PHPMD.ShortVariable)
     */
    public static function map(callable $f, $iterator)
    {
        return new MapIterator($f, self::toIterator($iterator));
    }

    /**
     * reverse
     *
     * ex)
     *
     * ``` php
     * $arr = [1, 2, 3];
     * $value = Collection::reverse($arr); // [3, 2, 1]
     * ```
     *
     * @param array|Traversable $iterator
     * @return array
     * @SuppressWarnings(PHPMD.ShortVariable)
     */
    public static function reverse($iterator)
    {
        return new \ArrayIterator(array_reverse(iterator_to_array(self::toIterator($iterator))));
    }

    /**
     * intersperse
     *
     * ex)
     *
     * ``` php
     * $arr = [1, 2, 3];
     * $value = Collection::intersperse(5, $arr); // [1, 5, 2, 5, 3]
     * ```
     *
     * ``` php
     * $value = Collection::intersperse(',', "test"); // "t,e,s,t"
     * $value = Collection::intersperse(',', ["Tom", "Jhon"]); // ["Tom", ",", "Jhon"]
     * ```
     *
     * @param mixed $sep
     * @param array|Traversable $iterator
     * @return Generator
     * @SuppressWarnings(PHPMD.ShortVariable)
     */
    public static function intersperse($sep, $iterator)
    {
        $it = self::toIterator($iterator);
        while ($it->valid()) {
            yield $it->current();
            $it->next();
            if ($it->valid()) {
                yield $sep;
            }
        }
    }

    /**
     * intercalate
     *
     * ex)
     *
     * ``` php
     * $arr = [[3, 4], [5, 6], [7, 8]];
     * $value = Collection::intercalate([1, 2], $arr); // [3, 4, 1, 2, 5, 6, 1, 2, 7, 8]
     * ```
     *
     * @param array|Traversable $xs
     * @param array|Traversable $xss
     * @return Generator
     * @SuppressWarnings(PHPMD.ShortVariable)
     */
    public static function intercalate($xs, $xss)
    {
        $it = self::toIterator($xss);
        while ($it->valid()) {
            foreach ($it->current() as $value) {
                yield $value;
            }
            $it->next();
            if ($it->valid()) {
                foreach ($xs as $x) {
                    yield $x;
                }
            }
        }
    }

    /**
     * transpose
     *
     * ex)
     *
     * ``` php
     * $arr = [[1, 2], [3, 4], [5, 6], [7, 8]];
     * $value = Collection::transpose($arr); // [[1, 3, 5, 7], [2, 4, 6, 8]]
     * ```
     *
     * @param array|Traversable $iterator
     * @return ArrayIterator
     * @SuppressWarnings(PHPMD.ShortVariable)
     */
    public static function transpose($iterator)
    {
        $it = self::toIterator($iterator);
        $result = [];
        foreach ($it as $xs) {
            $n = 0;
            foreach ($xs as $x) {
                $result[$n++][] = $x;
            }
        }
        return new \ArrayIterator($result);
    }

    /**
     * subsequences
     *
     * ex)
     *
     * ``` php
     * ```
     *
     * @param array|Traversable $xs
     * @return Generator
     * @SuppressWarnings(PHPMD.ShortVariable)
     */
    public static function subsequences($xs)
    {
        // todo@not implimented
    }

    /**
     * permutations
     *
     * ex)
     *
     * ``` php
     * ```
     *
     * @param array|Traversable $xss
     * @return Generator
     * @SuppressWarnings(PHPMD.ShortVariable)
     */
    public static function permutations($xs)
    {
        // todo@not implimented
    }

    // Reducing lists

    /**
     * fold
     *
     * ex)
     *
     * ``` php
     * ```
     *
     * @param callable $f
     * @param mixed $z
     * @param array|Traversable $xs
     * @return $mixed
     * @SuppressWarnings(PHPMD.ShortVariable)
     */
    public static function fold(callable $f, $z, $xs)
    {
        foreach ($xs as $x) {
            $z = call_user_func($f, $z, $x);
        }
        return $z;
    }

    /**
     * fold1
     *
     * ex)
     *
     * ``` php
     * ```
     *
     * @param callable $f
     * @param array|Traversable $xs
     * @return None|Some
     * @SuppressWarnings(PHPMD.ShortVariable)
     */
    public static function fold1(callable $f, $xs)
    {
        return self::uncons($xs)->map(function ($ys) use ($f) {
            $z = $ys[0];
            foreach ($ys[1] as $y) {
                $z = call_user_func($f, $z, $y);
            }
            return $z;
        });
    }

    // Special folds

    /**
     * concat
     *
     * ex)
     *
     * ``` php
     * ```
     *
     * @param array|Traversable $xss
     * @SuppressWarnings(PHPMD.ShortVariable)
     */
    public static function concat($xss)
    {
        foreach ($xss as $xs) {
            foreach ($xs as $x) {
                yield $x;
            }
        }
    }

    /**
     * concatMap
     *
     * ex)
     *
     * ``` php
     * ```
     *
     * @param callable $f
     * @param array|Traversable $xss
     * @SuppressWarnings(PHPMD.ShortVariable)
     */
    public static function concatMap(callable $f, $xss)
    {
        return self::concat(self::map($f, $xss));
    }

    /**
     * any
     *
     * ex)
     *
     * ``` php
     * ```
     *
     * @param callable $pred
     * @param array|Traversable $xs
     * @SuppressWarnings(PHPMD.ShortVariable)
     */
    public static function any(callable $pred, $xs)
    {
        foreach ($xs as $x) {
            if (call_user_func($pred, $x)) {
                return true;
            }
        }
        return false;
    }

    /**
     * all
     *
     * ex)
     *
     * ``` php
     * ```
     *
     * @param callable $pred
     * @param array|Traversable $xs
     * @SuppressWarnings(PHPMD.ShortVariable)
     */
    public static function all(callable $pred, $xs)
    {
        $ret = true;
        foreach ($xs as $x) {
            $ret = call_user_func($pred, $x) && $ret;
        }
        return $ret;
    }

    /**
     * sum
     *
     * ex)
     *
     * ``` php
     * ```
     *
     * @param array|Traversable $xs
     * @SuppressWarnings(PHPMD.ShortVariable)
     */
    public static function sum($xs)
    {
        return self::fold(function ($z, $x) { return $z + $x; }, 0, $xs);
    }

    /**
     * product
     *
     * ex)
     *
     * ``` php
     * ```
     *
     * @param array|Traversable $xs
     * @SuppressWarnings(PHPMD.ShortVariable)
     */
    public static function product($xs)
    {
        return self::fold(function ($z, $x) { return $z * $x; }, 1, $xs);
    }

    /**
     * maximum
     *
     * ex)
     *
     * ``` php
     * ```
     *
     * @param array|Traversable $xs
     * @SuppressWarnings(PHPMD.ShortVariable)
     */
    public static function maximum($xs)
    {
        foreach ($xs as $x) {
            if (!isset($max)) {
                $max = $x;
            }
            $max = max($max, $x);
        }
        return !isset($max)
            ? Option::None()
            : Option::Some($max);
    }

    /**
     * maximumBy
     *
     * ex)
     *
     * ``` php
     * ```
     *
     * @param callable $cmp
     * @param array|Traversable $xs
     * @SuppressWarnings(PHPMD.ShortVariable)
     */
    public static function maximumBy(callable $cmp, $xs)
    {
        foreach ($xs as $x) {
            if (!isset($max)) {
                $max = $x;
            }
            $max = call_user_func($cmp, $max, $x) === true
                ? $max
                : $x;
        }
        return !isset($max)
            ? Option::None()
            : Option::Some($max);
    }

    /**
     * minimum
     *
     * ex)
     *
     * ``` php
     * ```
     *
     * @param array|Traversable $xs
     * @SuppressWarnings(PHPMD.ShortVariable)
     */
    public static function minimum($xs)
    {
        foreach ($xs as $x) {
            if (!isset($min)) {
                $min = $x;
            }
            $min = min($min, $x);
        }
        return !isset($min)
            ? Option::None()
            : Option::Some($min);
    }

    /**
     * minimumBy
     *
     * ex)
     *
     * ``` php
     * ```
     *
     * @param callable $cmp
     * @param array|Traversable $xs
     * @SuppressWarnings(PHPMD.ShortVariable)
     */
    public static function minimumBy(callable $cmp, $xs)
    {
        foreach ($xs as $x) {
            if (!isset($min)) {
                $min = $x;
            }
            $min = call_user_func($cmp, $min, $x) === true
                ? $x
                : $min;
        }
        return !isset($min)
            ? Option::None()
            : Option::Some($min);
    }

    // Scans

    /**
     * scan
     *
     * ex)
     *
     * ``` php
     * ```
     *
     * @param callable $f
     * @param mixed $q
     * @param array|Traversable $xs
     * @SuppressWarnings(PHPMD.ShortVariable)
     */
    public static function scan(callable $f, $q, $xs)
    {
        yield $q;
        foreach (self::toIterator($xs) as $x) {
            $q = call_user_func($f, $q, $x);
            yield $q;
        }
    }

    /**
     * scan1
     *
     * ex)
     *
     * ``` php
     * ```
     *
     * @param callable $f
     * @param array|Traversable $xs
     * @SuppressWarnings(PHPMD.ShortVariable)
     */
    public static function scan1(callable $f, $xs)
    {
        return self::uncons($xs)->map(function ($ys) use ($f) {
            $q = $ys[0];
            yield $q;
            foreach ($ys[1] as $y) {
                $q = call_user_func($f, $q, $y);
                yield $q;
            }
        });
    }

    // Accumulating maps

    /**
     * mapAccum
     *
     * ex)
     *
     * ``` php
     * ```
     *
     * @param callable $f
     * @param mixed $z
     * @param array|Traversable $xs
     * @SuppressWarnings(PHPMD.ShortVariable)
     */
    public static function mapAccum(callable $f, $z, $xs)
    {
        $result = [];
        foreach ($xs as $x) {
            $q = call_user_func($f, $z, $x);
            $z = $q[0];
            $result[] = $q[1];
        }
        return [$z, $result];
    }

    // Infinite lists

    /**
     * iterate
     *
     * ex)
     *
     * ``` php
     * ```
     *
     * @param callable $f
     * @param mixed $z
     * @param array|Traversable $xs
     * @SuppressWarnings(PHPMD.ShortVariable)
     */
    public static function iterate(callable $f, $x)
    {
        $z = $x;
        yield $z;
        while (true) {
            $z = call_user_func($f, $z);
            yield $z;
        }
    }

    /**
     * repeat
     *
     * ex)
     *
     * ``` php
     * ```
     *
     * @param mixed $x
     * @return Generator
     * @SuppressWarnings(PHPMD.ShortVariable)
     */
    public static function repeat($x)
    {
        while (true) {
            yield $x;
        }
    }

    /**
     * replicate
     *
     * ex)
     *
     * ``` php
     * ```
     *
     * @param mixed $x
     * @return Generator
     * @SuppressWarnings(PHPMD.ShortVariable)
     */
    public static function replicate($n, $x)
    {
        return self::take($n, self::repeat($x));
    }

    /**
     * cycle
     *
     * ex)
     *
     * ``` php
     * ```
     *
     * @param array|Traversable $xs
     * @return Generator
     * @SuppressWarnings(PHPMD.ShortVariable)
     */
    public static function cycle($xs)
    {
        while (true) {
            foreach ($xs as $k => $v) {
                yield $k => $v;
            }
        }
    }

    // Unfolding

    /**
     * unfold
     *
     * ex)
     *
     * ``` php
     * ```
     *
     * @param callable $f
     * @param mixed $b
     * @return Generator
     * @SuppressWarnings(PHPMD.ShortVariable)
     */
    public static function unfold($f, $b)
    {
        while (true) {
            $value = call_user_func($f, $b);
            if ($value->isEmpty) {
                break;
            }
            $result = $value->getOrElse(null);
            yield $result[0];
            $b = $result[1];
        }
    }

    // Extracting sublists

    /**
     * take
     *
     * ex)
     *
     * ``` php
     * ```
     *
     * @param integer $n
     * @param array|Traversable $xs
     * @retun Generator
     * @SuppressWarnings(PHPMD.ShortVariable)
     */
    public static function take($n, $xs)
    {
        foreach (self::toIterator($xs) as $x) {
            if ($n-- <= 0) {
                break;
            }
            yield $x;
        }
    }

    /**
     * drop
     *
     * ex)
     *
     * ``` php
     * ```
     *
     * @param integer $n
     * @param array|Traversable $xs
     * @retun Generator
     * @SuppressWarnings(PHPMD.ShortVariable)
     */
    public static function drop($n, $xs)
    {
        foreach (self::toIterator($xs) as $x) {
            if ($n-- > 0) {
                continue;
            }
            yield $x;
        }
    }

    /**
     * filter
     *
     * ex)
     *
     * ``` php
     * $arr = [1, 2, 3];
     * $value = Collection::filter(function ($x) { return $x % 2 === 0; }, $arr); // [2]
     * ```
     *
     * @param callable $pred
     * @param mixed $iterator
     * @return FilterdIterator
     * @SuppressWarnings(PHPMD.ShortVariable)
     */
    public static function filter(callable $pred, $iterator)
    {
        return new \CallbackFilterIterator(self::toIterator($iterator), $pred);
    }

    /**
     * lookup
     *
     * ex)
     *
     * ``` php
     * $arr = ['val' => 1, 'val1' => 2];
     * $value = Collection::lookup('val1', $arr); // Some(2)
     * $value = Collection::lookup('none', $arr); // None
     * ```
     *
     * @param mixed $index
     * @param array|Traversable $iterator
     * @return None|Some
     */
    public static function lookup($index, $iterator)
    {
        foreach (self::toIterator($iterator) as $key => $value) {
            if ($key === $index) {
                return Option::Some($value);
            }
        }
        return Option::None();
    }
}
