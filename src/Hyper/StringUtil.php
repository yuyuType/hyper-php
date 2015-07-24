<?php
/**
 * Stringに対する操作関数群
 *
 * @package Hyper
 * @author yuyuType
 * @link https://github.com/yuyuType/hyper-php
 */

namespace Hyper;

use Hyper\Data\Option;

/**
 * Stringに対する操作関数群
 *
 * @author yuyuType
 */
class StringUtil
{
    /**
     * 連想配列のキーから値を取得する関数
     *
     * 例)
     *
     * ``` php
     * StringUtil::isDigit('12345');  // true
     * StringUtil::isDigit('0x1245'); // false
     * StringUtil::isDigit('abcdef'); // false
     * ```
     *
     * @param string $str 文字列
     * @return bool
     */
    public static function isDigit($str)
    {
        if (is_int($str)) {
            return true;
        } elseif (is_string($str)) {
            return ctype_digit($str);
        }
        return false;
    }
}
