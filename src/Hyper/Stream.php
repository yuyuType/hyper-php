<?php
/**
 * F#のpipeline oeratorのエミュレート
 *
 * @package Hyper
 * @author yuyuType
 * @link https://github.com/yuyuType/hyper-php
 */

namespace Hyper;

use Hyper\Enumerable;

/**
 * F#のpipeline oerator, Java Stream APIのエミュレート
 *
 * @author yuyuType
 */
class Stream
{
    /** 値をストリームに保存する */
    private $value;

    /**
     * 値をストリームに保存する
     */
    public function __construct($value)
    {
        $this->value = $value;
    }

    /**
     * head関数
     *
     * 例)
     *
     * ``` php
     * $value = (new Stream([1, 2, 3]))
     *     ->head(); // 1
     *     ->get();
     * ```
     *
     * @return Stream
     */
    public function head()
    {
        $this->value = Enumerable::head($this->value);
        return $this;
    }

    /**
     * last関数
     *
     * 例)
     *
     * ``` php
     * $value = (new Stream([1, 2, 3]))
     *     ->last(); // 3
     *     ->get();
     * ```
     *
     * @return Stream
     */
    public function last()
    {
        $this->value = Enumerable::last($this->value);
        return $this;
    }

    /**
     * map関数
     *
     * 例)
     *
     * ``` php
     * $value = (new Stream([1, 2, 3]))
     *     ->map(function ($x) { return $x * $x; }); // [1, 4, 9]
     *     ->get();
     * ```
     *
     * @param callable $f 写像関数
     * @return Stream
     * @SuppressWarnings(PHPMD.ShortVariable)
     */
    public function map(callable $f)
    {
        $this->value = Enumerable::map($f, $this->value);
        return $this;
    }

    /**
     * filter関数
     *
     * 例)
     *
     * ``` php
     * $value = (new Stream(range(1, 10)))
     *     ->map(function ($x) { return $x % 2 === 0; }) // [2, 4, 6, 8, 10]
     *     ->get();
     * ```
     *
     * @param callable $f 写像関数
     * @return Stream
     * @SuppressWarnings(PHPMD.ShortVariable)
     */
    public function filter(callable $f)
    {
        $this->value = Enumerable::filter($f, $this->value);
        return $this;
    }

    /**
     * 関数を束縛し、自身の持つ値を渡して実行する
     *
     * @param callable $f mixed f([mixed $...])
     * @param mixed $params,... $fに与えるパラメータの一部
     * @return Stream
     * @SuppressWarnings(PHPMD.ShortVariable)
     */
    public function bind(callable $f)
    {
        $args = func_get_args();
        $f = array_shift($args);
        $this->value = call_user_func_array($f, array_merge($args, [$this->value]));
        return $this;
    }

    /**
     * イテレータからarrayに変換して返す
     *
     * @return array
     */
    public function toArray()
    {
        return iterator_to_array($this->value);
    }

    /**
     * イテレータからstringに変換して返す
     *
     * @return mixed
     */
    public function toString()
    {
        return join($this->toArray());
    }

    /**
     * 値をストリームから取得する
     *
     * @return mixed
     */
    public function get()
    {
        return $this->value;
    }
}
