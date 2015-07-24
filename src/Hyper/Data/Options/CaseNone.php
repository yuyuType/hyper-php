<?php
/**
 * Option CaseNone
 *
 * @package Hyper\Data\Options
 * @auther yuyuType
 */

namespace Hyper\Data\Options;

use Hyper\Func;

/**
 * 失敗にマッチするクラス、Optionのmatch関数で利用する
 */
class CaseNone
{
    /** @var mixed 失敗時に返す値or実行する関数 */
    private $f;

    /**
     * 失敗時に返す値 or 実行する関数の保持
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
     * @return mixed
     */
    public function evaluate()
    {
        return Func::evaluate($this->f);
    }
}
