<?php
/**
 * Option Monad
 *
 * @package Hyper\Data
 * @auther yuyuType
 * @link https://github.com/yuyuType/hyper-php
 */

namespace Hyper\Data;

use Hyper\Data\Options\CaseNone;
use Hyper\Data\Options\CaseSome;
use Hyper\Data\Options\None;
use Hyper\Data\Options\Some;

/**
 * 成功または失敗を意味するクラス、成功時は値をひとつ持つクラス
 */
class Option
{
    /** @var None クラスを毎回newするのはパフォーマンス的に無駄なので最初から用意しておく */
    private static $none = null;

    // @codingStandardsIgnoreStart
    /**
     * 値をひとつ持つ成功を意味するクラスSomeを返す
     *
     * @param mixed $value 成功時の値
     * @return Some
     */
    public static function Some($value)
    {
        return new Some($value);
    }
    // @codingStandardsIgnoreEnd

    // @codingStandardsIgnoreStart
    /**
     * self::Someのalias
     *
     * @param mixed $value 成功時の値
     * @return Some
     */
    public static function OK($value)
    {
        return self::Some($value);
    }
    // @codingStandardsIgnoreEnd

    // @codingStandardsIgnoreStart
    /**
     * 失敗を意味するクラスNoneを返す
     *
     * @return None
     */
    public static function None()
    {
        if (is_null(self::$none)) {
            self::$none = new None();
        }
        return self::$none;
    }
    // @codingStandardsIgnoreEnd

    // @codingStandardsIgnoreStart
    /**
     * self::Noneのalias
     *
     * @return None
     */
    public static function NG()
    {
        return self::None();
    }
    // @codingStandardsIgnoreEnd

    /**
     * 成功とマッチするクラスを返す
     *
     * Some or Noneクラスのmatch関数で利用する
     * 例)
     * ```php
     * Option::Some(3)->match(
     *     Option::caseNone(0),
     *     Option::caseSome(function ($x) { return $x * 2; })
     * );
     * ```
     * 結果は6
     * ```php
     * Option::None()->match(
     *     Option::caseNone(3),
     *     Option::caseSome(function ($x) { return $x * 2; })
     * );
     * ```
     * 結果は3
     *
     * @param mixed|callable $value 成功時の値、または関数 value(mixed $x) : mixed
     * @return CaseSome
     */
    public static function caseSome($value)
    {
        return new CaseSome($value);
    }

    /**
     * self::caseSomeのalias
     *
     * @param mixed|callable $value 成功時の値、または関数 value(mixed $x) : mixed
     * @return CaseSome
     */
    public static function caseOK($value)
    {
        return self::caseSome($value);
    }

    /**
     * 失敗とマッチするクラスを返す
     *
     * Some or Noneクラスのmatch関数で利用する
     * ```php
     * Option::Some(3)->match(
     *     Option::caseNone(0),
     *     Option::caseSome(function ($x) { return $x * 2; })
     * );
     * ```
     * 結果は6
     * ```php
     * Option::None()->match(
     *     Option::caseNone(3),
     *     Option::caseSome(function ($x) { return $x * 2; })
     * );
     * ```
     * 結果は3
     *
     *
     * @param mixed|callable $value 失敗時の値、または関数 value() : mixed
     * @return CaseNone
     */
    public static function caseNone($value)
    {
        return new CaseNone($value);
    }

    /**
     * self::caseNoneのalias
     *
     * @param mixed|callable $value 失敗時の値、または関数 value() : mixed
     * @return CaseNone
     */
    public static function caseNG($value)
    {
        return self::caseNone($value);
    }
}
