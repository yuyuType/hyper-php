<?php
/**
 * Option Some
 *
 * @package Hyper\Data\Options
 * @auther yuyuType
 */

namespace Hyper\Data\Options;

use Hyper\Data\Options\IOption;
use Hyper\Func;

/**
 * 成功を意味するクラス、成功時の値をひとつ持つ
 *
 * @property-read bool $isEmpty
 */
class Some implements IOption
{
    /** @var bool 空かどうかの判定用常にFalseを返す */
    public $isEmpty = false;
    /** $var mixed 成功時の値 */
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
     * @param mixed|callable $default 失敗時に返す値、または関数 default() : mixed
     * @return mixed 成功時の値
     */
    public function getOrElse($default)
    {
        return $this->value;
    }

    /**
     * {@inheritdoc}
     *
     * @param None|Some|callable $opt None or Someのインスタンス、<br>
     * または関数 opt() : None|Some
     * @return Some
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
     * @return Some
     */
    public function map(callable $success)
    {
        /*
         * $successは引数を一つだけ受け取る関数しか渡せない
         * $successに２つ以上引数を取る関数を渡して最後の一つだけ
         * Someに包まれた値を渡したい場合には不便なので
         * その状況に対応出来るように実装してある
         *
         * 未対応の場合Func::bindを使って引数を適用した状態で渡すことで
         * 同様の動作をするがFunc::bindなしでも実現できたほうが便利なため
         * func_get_argsを使って実装する
         *
         * 例)
         * ```php
         * Option::Some(3)->map(function ($x, $y, $z) { return $x * $y * $z; }, 1, 2);
         * ```
         * 結果は6
         * $successが２つ以上引数を取る関数に未対応の場合はこのようにする必要がある
         * ```php
         * Option::Some(3)->map(Fun::bind(function ($x, $y, $z) { return $x * $y * $z; }, 1, 2));
         * ```
         * 結果は6
         */
        $args = func_get_args();
        $success = array_shift($args);
        return new Some(call_user_func_array($success, array_merge($args, [$this->value])));
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
        return call_user_func($success, $this->value);
    }

    /**
     * {@inheritdoc}
     *
     * @return None|Some
     *
     * @throws \LogicException 保持している値がIOptionのインスタンスではない場合
     */
    public function flatten()
    {
        if ($this->value instanceof IOption) {
            return $this->value;
        }
        throw new \LogicException("Some's value is not Option Type.");
    }

    /**
     * {@inheritdoc}
     *
     * @param callable $f mixed f(mixed $x) : None|Some
     * @return None|Some
     */
    public function flatMap(callable $f)
    {
        return call_user_func_array([$this, 'map'], func_get_args())->flatten();
    }

    /**
     * {@inheritdoc}
     *
     * @param None|Some|callable $opt None or Someクラスのインスタンス、<br>
     * または関数 opt() None|Some
     * @return None|Some
     */
    public function ap($opt)
    {
        // $optがOptionを返す引数なし関数だった場合を考慮してFunc::evaluateを挟んで処理する
        return $this->flatMap(function ($f) use ($opt) {
            return Func::evaluate($opt)->map($f);

        });
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
         * Option::Some(3)->match(
         *     Option::caseSome(function ($x) { return $x * 2; }),
         *     Option::caseNone(0)
         * );
         * ```
         * 結果は6
         * ```php
         * Option::Some(3)->match(
         *     Option::caseNone(0),
         *     Option::caseSome(function ($x) { return $x * 2; })
         * );
         * ```
         * 結果は6
         */
        $args = func_get_args();
        foreach ($args as $caseObj) {
            if ($caseObj instanceof CaseSome) {
                return $caseObj->evaluate($this->value);
            }
        }
        throw new \LogicException("You must set caseSome parameter.");
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
