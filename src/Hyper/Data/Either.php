<?php
/**
 * Either Monad
 *
 * @package Hyper\Data
 * @auther yuyuType
 * @link https://github.com/yuyuType/hyper-php
 */

namespace Hyper\Data;

use Hyper\Data\Eithers\CaseLeft;
use Hyper\Data\Eithers\CaseRight;
use Hyper\Data\Eithers\Left;
use Hyper\Data\Eithers\Right;

/**
 * 成功または失敗を意味するクラス、それぞれ値をひとつ持つクラス
 */
class Either
{
    // @codingStandardsIgnoreStart
    /**
     * 値をひとつ持つ成功を意味するクラスRightを返す
     *
     * @param mixed $value 成功時の値
     * @return Right
     */
    public static function Right($value)
    {
        return new Right($value);
    }
    // @codingStandardsIgnoreEnd

    // @codingStandardsIgnoreStart
    /**
     * self::Rightのalias
     *
     * @param mixed $value 成功時の値
     * @return Right
     */
    public static function Success($value)
    {
        return self::Right($value);
    }
    // @codingStandardsIgnoreEnd

    // @codingStandardsIgnoreStart
    /**
     * 失敗を意味するクラスLeftを返す
     *
     * @param mixed $value 失敗時の値
     * @return Left
     */
    public static function Left($value)
    {
        return new Left($value);
    }
    // @codingStandardsIgnoreEnd

    // @codingStandardsIgnoreStart
    /**
     * self::Leftのalias
     *
     * @param mixed $value 失敗時の値
     * @return Left
     */
    public static function Failure($value)
    {
        return self::Left($value);
    }
    // @codingStandardsIgnoreEnd

    /**
     * 成功とマッチするクラスを返す
     *
     * Right or Leftクラスのmatch関数で利用する
     * 例)
     * ```php
     * Either::Right(3)->match(
     *     Either::caseLeft('fail'),
     *     Either::caseRight(function ($x) { return $x * 2; })
     * );
     * ```
     * 結果は6
     * ```php
     * Either::Left('fail')->match(
     *     Either::caseLeft('fail2'),
     *     Either::caseRight(function ($x) { return $x * 2; })
     * );
     * ```
     * 結果は'fail2'
     *
     * @param mixed|callable $value 成功時の値、または関数 value(mixed $x) : mixed
     * @return CaseRight
     */
    public static function caseRight($value)
    {
        return new CaseRight($value);
    }

    /**
     * self::caseRightのalias
     *
     * @param mixed|callable $value 成功時の値、または関数 value(mixed $x) : mixed
     * @return CaseRight
     */
    public static function caseSuccess($value)
    {
        return self::caseRight($value);
    }

    /**
     * 失敗とマッチするクラスを返す
     *
     * Right or Leftクラスのmatch関数で利用する
     * 例)
     * ```php
     * Either::Right(3)->match(
     *     Either::caseLeft('fail'),
     *     Either::caseRight(function ($x) { return $x * 2; })
     * );
     * ```
     * 結果は6
     * ```php
     * Either::Left('fail')->match(
     *     Either::caseLeft('fail2'),
     *     Either::caseRight(function ($x) { return $x * 2; })
     * );
     * ```
     * 結果は'fail2'
     *
     * @param mixed|callable $value 失敗時の値、または関数 value(mixed $x) : mixed
     * @return CaseLeft
     */
    public static function caseLeft($value)
    {
        return new CaseLeft($value);
    }

    /**
     * self::caseLeftのalias
     *
     * @param mixed|callable $value 失敗時の値、または関数 value(mixed $x) : mixed
     * @return CaseLeft
     */
    public static function caseFailure($value)
    {
        return self::caseLeft($value);
    }
}
