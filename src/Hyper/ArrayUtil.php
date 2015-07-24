<?php
/**
 * arrayに対する操作関数群
 *
 * @package Hyper
 * @author yuyuType
 * @link https://github.com/yuyuType/hyper-php
 */

namespace Hyper;

use Hyper\Data\Option;

/**
 * arrayに対する操作関数群
 *
 * @author yuyuType
 */
class ArrayUtil
{
    /**
     * 連想配列のキーから値を取得する関数
     *
     * 例)
     *
     * ``` php
     * $arr = ['val' => 1, 'val1' => 2];
     * $value = ArrayUtil::lookup('val1', $arr); // Some(2)
     * $value = ArrayUtil::lookup('none', $arr); // None
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
