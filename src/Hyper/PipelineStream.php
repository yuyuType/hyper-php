<?php
/**
 * F#のpipeline oeratorのエミュレート
 *
 * @package Hyper
 * @author yuyuType
 * @link https://github.com/yuyuType/hyper-php
 */

namespace Hyper;

/**
 * F#のpipeline oeratorのエミュレート
 *
 * @author yuyuType
 */
class PipelineStream
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
     * 関数を束縛し、自身の持つ値を渡して実行する
     *
     * @param callable $f mixed f([mixed $...])
     * @param mixed $params,... $fに与えるパラメータの一部
     * @return PipelineStream
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
     * 値をストリームから取得する
     *
     * @return mixed
     */
    public function get()
    {
        return $this->value;
    }
}
