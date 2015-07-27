<?php
/**
 * arrayに対する操作関数群
 *
 * @package Hyper
 * @author yuyuType
 * @link https://github.com/yuyuType/hyper-php
 */

namespace Hyper;

use Hyper\Data\Enumerator\FilterdIterator;
use Hyper\Data\Enumerator\MapIterator;
use Hyper\Data\Enumerator\StringIterator;
use Hyper\Data\Option;

/**
 * arrayに対する操作関数群
 *
 * @author yuyuType
 */
class Enumerable
{
    /**
     * イテレータに変換する
     *
     * @param mixed $iterator イテレータに変換する対象
     * @return mixed イテレータ
     * @throws \LogicException 保持している値がIEitherのインスタンスではない場合
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
     * 先頭を返す関数
     *
     * @param mixed $iterator イテレータ
     * @return mixed 値
     * @SuppressWarnings(PHPMD.ShortVariable)
     */
    public static function head($iterator)
    {
        // 確実にheadを取る為にrewindする、
        // iteratorの状態が変更されるので注意。
        $it = self::toIterator($iterator);
        $it->rewind();
        $result = $it->current();
        return $result === null ? Option::None() : Option::Some($result);
    }

    /**
     * 最後を返す関数
     *
     * @param mixed $iterator イテレータ
     * @return mixed 値
     * @SuppressWarnings(PHPMD.ShortVariable)
     */
    public static function last($iterator)
    {
        // 確実にlastを取る為に、
        // iteratorの状態が変更されるので注意。
        $it = self::toIterator($iterator);
        $it->rewind();
        if ($it->valid() === false) {
            return Option::None();
        }
        foreach ($it as $value) {}
        return Option::Some($value);
    }

    /**
     * map関数
     *
     * 例)
     *
     * ``` php
     * $arr = [1, 2, 3];
     * $value = Enumerable::map(function ($x) { return $x * $x; }, $arr); // [1, 4, 9]
     * ```
     *
     * @param callable $f 写像関数
     * @param mixed $iterator 写像先
     * @return MapIterator 写像したイテレータ
     * @SuppressWarnings(PHPMD.ShortVariable)
     */
    public static function map(callable $f, $iterator = null)
    {
        return $iterator === null
            ? function ($iterator) use ($f) {
                return new MapIterator($f, self::toIterator($iterator));
            }
            : new MapIterator($f, self::toIterator($iterator));
    }

    /**
     * filter関数
     *
     * 例)
     *
     * ``` php
     * $arr = [1, 2, 3];
     * $value = Enumerable::filter(function ($x) { return $x % 2 === 0; }, $arr); // [2]
     * ```
     *
     * @param callable $pred 条件関数
     * @param mixed $iterator イテレータ
     * @return FilterdIterator 写像したイテレータ
     * @SuppressWarnings(PHPMD.ShortVariable)
     */
    public static function filter(callable $f, $iterator = null)
    {
        return $iterator === null
            ? function ($iterator) use ($f) {
                return new FilterdIterator($f, self::toIterator($iterator));
            }
            : new FilterdIterator($f, self::toIterator($iterator));
    }

    /**
     * 連想配列のキーから値を取得する関数
     *
     * 例)
     *
     * ``` php
     * $arr = ['val' => 1, 'val1' => 2];
     * $value = Enumerable::lookup('val1', $arr); // Some(2)
     * $value = Enumerable::lookup('none', $arr); // None
     * ```
     *
     * @param mixed $index 探す対象のキー
     * @param array $arr array
     * @return None|Some
     */
    public static function lookup($index, $arr)
    {
        return isset($arr[$index])
            ? Option::Some($arr[$index])
            : Option::None();
    }
}
