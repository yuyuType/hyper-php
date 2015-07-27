<?php
/**
 * Either Left
 *
 * @package Hyper\Data\Eithers
 * @auther yuyuType
 */


namespace Hyper\Data\Eithers;

use Hyper\Data\Eithers\IEither;
use Hyper\Func;

/**
 * 失敗を意味するクラス、失敗時の値をひとつ持つ
 */
class Left implements IEither
{
    /** @var bool Leftかどうかの判定用 */
    public $isLeft = true;
    /** @var bool Rightかどうかの判定用 */
    public $isRight = false;
    /** @var mixed 成功時の値 */
    private $value;

    /**
     * 失敗時の値を保持する
     *
     * @param mixed $value 失敗時の値
     * @return void
     */
    public function __construct($value)
    {
        $this->value = $value;
    }

    /**
     * {@inheritdoc}
     *
     * @param callable $fail fail(mixed $x) : mixed
     * @return mixed
     */
    public function getOrElse(callable $fail)
    {
        return call_user_func($fail, $this->value);
    }

    /**
     * {@inheritdoc}
     *
     * @param Left|Right|callable $opt Either::Left or Either::Rightのインスタンス、<br>
     * または関数 opt() : Left|Right
     * @return Left|Right
     */
    public function orElse($opt)
    {
        // $optがEitherを返す引数なし関数だった場合を考慮してFunc::evaluateを挟んで処理する
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
     * @return Left mixed
     */
    public function map(callable $success)
    {
        return $this;
    }

    /**
     * {@inheritdoc}
     *
     * @param callable $fail fail(mixed $x) : mixed
     * @param callable $success success(mixed $x) : mixed
     * @return mixed
     */
    public function fold(callable $fail, callable $success)
    {
        return call_user_func($fail, $this->value);
    }

    /**
     * {@inheritdoc}
     *
     * @return Left|Right
     */
    public function flatten()
    {
        return $this;
    }

    /**
     * {@inheritdoc}
     *
     * @param callable $f mixed f(mixed $x) : Left|Right
     * @return Left|Right
     */
    public function flatMap(callable $f)
    {
        return $this;
    }

    /**
     * {@inheritdoc}
     *
     * @param Left|Right|callable $opt Left or Rightクラスのインスタンス、<br>
     * または関数 opt() Left|Right
     * @return Left|Right
     */
    public function ap($opt)
    {
        return $this;
    }

    /**
     * {@inheritdoc}
     *
     * @param CaseLeft|CaseRight $params,... 失敗時の値を取って処理するクラスのインスタンス<br>
     * 成功時の値を取って処理するクラスのインスタンス
     * @return mixed
     *
     * @throws \LogicException CaseLeftクラスのインスタンスがパラメータにない場合
     */
    public function match()
    {
        /*
         * 順不同でも呼べるようにfunc_get_argsを使って引数を取得する
         * 具体的に示すと下記コードがどちらも動くようにしている
         *
         * 例)
         * ```php
         * Either::Left('fail')->match(
         *     Either::caseRight(function ($x) { return $x * 2; }),
         *     Either::caseLeft(0)
         * );
         * ```
         * 結果は0
         * ```php
         * Either::Left('fail')->match(
         *     Either::caseLeft(0),
         *     Either::caseRight(function ($x) { return $x * 2; })
         * );
         * ```
         * 結果は0
         */
        $args = func_get_args();
        foreach ($args as $caseObj) {
            if ($caseObj instanceof CaseLeft) {
                return $caseObj->evaluate($this->value);
            }
        }
        throw new \LogicException("You must set caseLeft parameter.");
    }

    /**
     * {@inheritdoc}
     *
     * @param callable $pred 条件関数
     * @return bool
     */
    public function exists(callable $pred)
    {
        return call_user_func($pred, $this->value);
    }
}
