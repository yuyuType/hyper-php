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
        return array_reverse(iterator_to_array(self::toIterator($iterator)));
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
