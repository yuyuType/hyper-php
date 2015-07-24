<?php
/**
 * Option None
 *
 * @package Hyper\Data\Options
 * @auther yuyuType
 */

namespace Hyper\Data\Options;

use Hyper\Data\Options\IOption;
use Hyper\Func;

/**
 * 失敗を意味するクラス、値は持たない
 */
class None implements IOption
{
    /** @var bool 空かどうかの判定用常にTrueを返す */
    public $isEmpty = true;

    /**
     * {@inheritdoc}
     *
     * @param mixed|callable $default 失敗時に返す値、または関数 default() : mixed
     * @return mixed $defaultまたは関数 default() : mixedの戻り値
     */
    public function getOrElse($default)
    {
        // $defaultが引数なし関数だった場合を考慮してFunc::evaluateを挟んで処理する
        return Func::evaluate($default);
    }

    /**
     * {@inheritdoc}
     *
     * @param None|Some|callable $opt None or Someのインスタンス、<br>
     * または関数 opt() : None|Some
     * @return None|Some
     */
    public function orElse($opt)
    {
        // $optがOptionを返す引数なし関数だった場合を考慮してFunc::evaluateを挟んで処理する
        return Func::evaluate($opt);
    }

    // @codingStandardsIgnoreStart
    /**
     * {@inheritdoc}
     *
     * @param callable $f f(mixed $x) : void
     * @return void
     *
     * @SuppressWarnings(PHPMD.CamelCaseMethodName)
     */
    public function each(callable $f)
    {
    }
    // @codingStandardsIgnoreEnd

    /**
     * {@inheritdoc}
     *
     * @param callable $success success(mixed $x) : mixed
     * @return None
     */
    public function map(callable $success)
    {
        return $this;
    }

    /**
     * {@inheritdoc}
     *
     * @param mixed|callable $fail 失敗時に返す値、または関数 fail() : mixed
     * @param callable $success success(mixed $x) : mixed
     * @return mixed
     */
    public function fold($fail, callable $success)
    {
        // $failが引数なし関数だった場合を考慮してFunc::evaluateを挟んで処理する
        return Func::evaluate($fail);
    }

    /**
     * {@inheritdoc}
     *
     * @return None
     */
    public function flatten()
    {
        return $this;
    }

    /**
     * {@inheritdoc}
     *
     * @param callable $f mixed f(mixed $x) : None|Some
     * @return None
     */
    public function flatMap(callable $f)
    {
        return $this;
    }

    /**
     * {@inheritdoc}
     *
     * @param None|Some|callable $opt None or Someクラスのインスタンス、<br>
     * または関数 opt() None|Some
     * @return None
     */
    public function ap($opt)
    {
        return $this;
    }

    /**
     * {@inheritdoc}
     *
     * @param CaseLeft|CaseRight $params,... 失敗時の処理をするクラスのインスタンス<br>
     * 成功時の値を取って処理するクラスのインスタンス
     * @return mixed
     *
     * @throws \LogicException CaseNoneクラスのインスタンスがパラメータにない場合
     */
    public function match()
    {
        /*
         * 順不同でも呼べるようにfunc_get_argsを使って引数を取得する
         * 具体的に示すと下記コードがどちらも動くようにしている
         *
         * 例)
         * ```php
         * Option::None()->match(
         *     Option::caseSome(function ($x) { return $x * 2; }),
         *     Option::caseNone(0)
         * );
         * ```
         * 結果は0
         * ```php
         * Option::None()->match(
         *     Option::caseNone(0),
         *     Option::caseSome(function ($x) { return $x * 2; })
         * );
         * ```
         * 結果は0
         */
        $args = func_get_args();
        foreach ($args as $caseObj) {
            if ($caseObj instanceof CaseNone) {
                return $caseObj->evaluate();
            }
        }
        throw new \LogicException("You must set caseNone parameter.");
    }
}
