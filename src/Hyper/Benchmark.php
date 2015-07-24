<?php
/**
 * ベンチマーク計測
 *
 * @package Hyper
 * @author yuyuType
 * @link https://github.com/yuyuType/hyper-php
 */

namespace Hyper;

/**
 * ベンチマーク計測
 *
 * @author yuyuType
 */

class Benchmark
{
    /**
     * 実行時間計測
     *
     * @param callable $f mixed f([mixed $...])
     * @return float
     * @SuppressWarnings(PHPMD.ShortVariable)
     */
    public static function measure(callable $f)
    {
        $timeStart = microtime(true);
        call_user_func($f);
        return microtime(true) - $timeStart;
    }
}
