<?php
/**
 * Either Right
 *
 * @package Hyper\Data\Eithers
 * @auther yuyuType
 */

namespace Hyper\Data\Eithers;

use Hyper\Data\Eithers\IEither;
use Hyper\Func;

/**
 * 成功を意味するクラス、成功時の値をひとつ持つ
 */
class Right implements IEither
{
    /** boolean Leftかどうかの判定用 */
    public $isLeft = false;
    /** boolean Rightかどうかの判定用 */
    public $isRight = true;
    /** mixed 成功時の値 */
    private $value;

    /**
     * 成功時の値を保持する
     *
     * @param mixed $value 成功時の値
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
        return $this->value;
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
        return $this;
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
        call_user_func($f, $this->value);
    }
    // @codingStandardsIgnoreEnd

    /**
     * {@inheritdoc}
     *
     * @param callable $success success(mixed $x) : mixed
     * @return Right
     */
    public function map(callable $success)
    {
        /*
         * $successは引数を一つだけ受け取る関数しか渡せない
         * $successに２つ以上引数を取る関数を渡して最後の一つだけ
         * Rightに包まれた値を渡したい場合には不便なので
         * その状況に対応出来るように実装してある
         *
         * 未対応の場合Func::bindを使って引数を適用した状態で渡すことで
         * 同様の動作をするがFunc::bindなしでも実現できたほうが便利なため
         * func_get_argsを使って実装する
         *
         * 例)
         * ---
         * Either::Right(3)->map(function ($x, $y, $z) { return $x * $y * $z; }, 1, 2);
         * 結果は6
         * ---
         * $successが２つ以上引数を取る関数に未対応の場合はこのようにする必要がある
         * Either::Right(3)->map(Fun::bind(function ($x, $y, $z) { return $x * $y * $z; }, 1, 2));
         * 結果は6
         * ---
         */
        $args = func_get_args();
        $success = array_shift($args);
        return new Right(call_user_func_array($success, array_merge($args, [$this->value])));
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
        return call_user_func($success, $this->value);
    }

    /**
     * {@inheritdoc}
     *
     * @return Left|Right
     *
     * @throws \LogicException 保持している値がIEitherのインスタンスではない場合
     */
    public function flatten()
    {
        if ($this->value instanceof IEither) {
            return $this->value;
        }
        throw new \LogicException("Right's value is not Either Type.");
    }

    /**
     * {@inheritdoc}
     *
     * @param callable $f mixed f(mixed $x) : Left|Right
     * @return Left|Right
     */
    public function flatMap(callable $f)
    {
        return call_user_func_array([$this, 'map'], func_get_args())->flatten();
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
        // $optがEitherを返す引数なし関数だった場合を考慮してFunc::evaluateを挟んで処理する
        return $this->flatMap(function ($f) use ($opt) {
            return Func::evaluate($opt)->map($f);
        });
    }

    /**
     * {@inheritdoc}
     *
     * @param CaseLeft|CaseRight $params,... 失敗時の値を取って処理するクラスのインスタンス<br>
     * 成功時の値を取って処理するクラスのインスタンス
     * @return mixed
     *
     * @throws \LogicException CaseRightクラスのインスタンスがパラメータにない場合
     */
    public function match()
    {
        /*
         * 順不同でも呼べるようにfunc_get_argsを使って引数を取得する
         * 具体的に示すと下記コードがどちらも動くようにしている
         *
         * 例)
         * ```php
         * Either::Right(3)->match(
         *     Either::caseRight(function ($x) { return $x * 2; }),
         *     Either::caseLeft(0)
         * );
         * ```
         * 結果は6
         * ```php
         * Either::Right(3)->match(
         *     Either::caseLeft(0),
         *     Either::caseRight(function ($x) { return $x * 2; })
         * );
         * ```
         * 結果は6
         */
        $args = func_get_args();
        foreach ($args as $caseObj) {
            if ($caseObj instanceof CaseRight) {
                return $caseObj->evaluate($this->value);
            }
        }
        throw new \LogicException("You must set caseRight parameter.");
    }

    /**
     *
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
