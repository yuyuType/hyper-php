<?php
/**
 * Option CaseRight
 *
 * @package Hyper\Data\Eithers
 * @auther yuyuType
 */

namespace Hyper\Data\Eithers;

/**
 * 成功にマッチするクラス、Eitherのmatch関数で利用する
 */
class CaseRight
{
    /** @var mixed $f 成功時に返す値or実行する関数 */
    private $f;

    /**
     * 成功時に返す値 or 実行する関数の保持
     *
     * @param mixed $f 失敗時に返す値 or 実行する関数 f() : mixed
     * @return void
     */
    public function __construct($f)
    {
        $this->f = $f;
    }

    /**
     * 評価
     *
     * @param mixed|callable $value 成功時の値
     * @return mixed
     */
    public function evaluate($value)
    {
        if (is_callable($this->f)) {
            return call_user_func($this->f, $value);
        }
        return $this->f;
    }
}
