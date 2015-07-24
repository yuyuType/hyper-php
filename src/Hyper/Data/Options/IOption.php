<?php
/**
 * Option Monad
 *
 * @package Hyper\Data\Options
 * @auther yuyuType
 */

namespace Hyper\Data\Options;

/**
 * 成功または失敗を意味し、成功時の値をひとつ持つインタフェース
 */
interface IOption
{
    /**
     * 成功時はその値を、失敗時は$defaultを返す
     *
     * 例)
     * ```php
     * Option::Some(100)->getOrElse(0);
     * ```
     * 結果は100
     * ```php
     * Option::None()->getOrElse(0);
     * ```
     * 結果は0
     *
     * @param mixed|callable $default 失敗時に返す値、または関数 default() : mixed
     * @return mixed $defaultまたは関数 default() : mixedの戻り値
     */
    public function getOrElse($default);

    /**
     * 成功の場合は自分自身を返す、失敗の場合は引数に取ったOptionを返す
     *
     * 例)
     * ```php
     * Option::Some(100)->orElse(Option::Some(99));
     * ```
     * 結果はSome(100)
     * ```php
     * Option::None()->orElse(Option::Some(99));
     * ```
     * 結果はSome(99)
     * ```php
     * Option::None()->orElse(Option::None());
     * ```
     * 結果はNone
     *
     * @param None|Some|callable $opt None or Someのインスタンス、<br>
     * または関数 opt() : None|Some
     * @return None|Some
     */
    public function orElse($opt);

    // @codingStandardsIgnoreStart
    /**
     * 成功の場合は自身の値をfに定期用する、失敗時は何もしない
     *
     * 更に解説すると、eachはforeachと同様のことをしているとみなすことが出来る
     * つまりSome(100)をarray(100)と同じとしてみると
     * ```php
     * foreach(array(100) as $x) { print_r($x); } // 100
     * foreach(array() as $x) { print_r($x); } // なにも表示されない
     * ```
     * に対し
     * ```php
     * Option::Some(100)->each('print_r'); // 100
     * Option::None()->each('print_r'); // なにも表示されない
     * ```
     * と見ることが出来てarrayとSomeまたはNoneが置き換わっただけと考えられる
     *
     * 例)
     * ```php
     * Option::Some(100)->each('print_r');
     * ```
     * 100がコンソールに表示される
     * ```php
     * Option::None()->each('print_r');
     * ```
     * なにも表示されない
     *
     * @param callable $f f(mixed $x) : void
     * @return void
     *
     * @SuppressWarnings(PHPMD.CamelCaseMethodName)
     */
    public function each(callable $f);
    // @codingStandardsIgnoreEnd

    /**
     * 成功時の値を変換してOptionに包んで返す、失敗時はなにもせず自分自身(None)を返す
     *
     * 更に解説すると、mapはarray_mapと同様のことをしているとみなすことが出来る
     * つまりSome(3)をarray(3)と同じとしてみると
     * ```php
     * array_map(function ($x) { return $x * 2; } array(3)); // array(6)
     * array_map(function ($x) { return $x * 2; } array()); // array()
     * ```
     * に対し
     * ```php
     * Option::Some(3)->map(function ($x) { return $x * 2; }); // Some(6)
     * Option::None()->map(function ($x) { return $x * 2; }); // None
     * ```
     * と見ることが出来てarrayとSomeまたはNoneが置き換わっただけと考えられる
     *
     * 例)
     * ```php
     * Option::Some(3)->map(function ($x) { return $x * 2; });
     * ```
     * 結果はSome(6)
     * ```php
     * Option::None()->map(function ($x) { return $x * 2; });
     * ```
     * 結果はNone
     *
     * @param callable $success success(mixed $x) : mixed
     * @return None|Some
     */
    public function map(callable $success);

    /**
     * 成功時は$successの戻り値を、失敗時は$failを返す
     *
     * ```php
     * Option::Some('test')->map('strtoupper')->getOrElse('None');
     * ```
     * と
     * ```php
     * Option::Some('test')->fold('None', 'strtoupper');
     * ```
     * は同じ
     *
     * 更に解説すると、foldはarray_reduceと同様のことをしているとみなすことが出来る
     * つまりSome(100)をarray(100)と同じとしてみると
     * ```php
     * array_reduce(function ($x, $y) { return $x + $y; }, 0, array(100)); // 100
     * array_reduce(function ($x, $y) { return $x + $y; }, 0, array()); // 0
     * ```
     * に対し
     * ```php
     * Option::Some(100)->fold(0, function ($x) { return $x; }); // 100
     * Option::None()->fold(0, function ($x) { return $x; }); // 0
     * ```
     * と見ることが出来て引数の順番や第二引数に渡す関数のパラメータは１つとなっているが
     * arrayとSomeまたはNoneが置き換わっただけと考えられる
     * Optionはひとつしか値を持たないので第二引数の関数はパラメータを一つしか取らない
     *
     * @param mixed|callable $fail 失敗時に返す値、または関数 fail() : mixed
     * @param callable $success success(mixed $x) : mixed
     * @return mixed
     */
    public function fold($fail, callable $success);

    /**
     * 平坦化
     *
     * Some(Some('hoge'))をSome('hoge')に平坦化して返す
     * Some(None())の場合はNoneを返す
     * None()の場合もNoneを返す
     *
     * @return None|Some
     *
     * @throws \LogicException 保持している値がIOptionのインスタンスではない場合
     */
    public function flatten();

    /**
     * flattenとmapの組み合わせ
     *
     * @param callable $f mixed f(mixed $x) : None|Some
     * @return None|Some
     */
    public function flatMap(callable $f);

    /**
     * applicativeスタイル
     *
     * ２つ以上引数を取る関数をmapに渡して残りの引数をapで適用する形のこと
     *
     * 例)
     * ```php
     * $optPattern = Option::Some('/(\w+) (\d+), (\d+)/i');
     * $optReplacement = Option::Some('${1}1,$3');
     * $optString = Option::Some('April 15, 2003');
     * $optPattern->map(Func::curry('preg_replace'))->ap($optReplacement)->ap($optString);
     * ```
     * 結果はSome('April1,2003')
     *
     * Func::curryは引数を１つずつ適用して呼び出すことが出来るようにする関数である
     * mapは引数を１つのみ受け取る関数しか渡せない為一度Func::curryを挟む必要がある
     *
     * apがない場合flatMapとmapを組み合わせて以下の様に書く必要がある
     *
     * ```php
     * $optPattern = Option::Some('/(\w+) (\d+), (\d+)/i');
     * $optReplacement = Option::Some('${1}1,$3');
     * $optString = Option::Some('April 15, 2003');
     * $optPattern->flatMap(function ($pattern) use($optReplacement, $optString) {
     *     return $optReplacement->flatMap(function ($replacement) use($pattern, $optString) {
     *         return $optString->map(function ($string) use($pattern, $replacement) {
     *             return preg_replace($pattern, $replacement, $string);
     *         });
     *     })
     * });
     * ```
     * 結果はSome('April1,2003')
     *
     * これはクロージャが引数の数分だけネストしていて読みづらいのでapを用意する
     *
     * @param None|Some|callable $opt None or Someクラスのインスタンス、<br>
     * または関数 opt() None|Some
     * @return None|Some
     */
    public function ap($opt);

    /**
     * 成功または失敗にマッチし、その時に行いたい処理を評価して返す
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
     * Option::None()->match(
     *     Option::caseSome(function ($x) { return $x * 2; }),
     *     Option::caseNone(3)
     * );
     * ```
     * 結果は3
     *
     * @param CaseLeft|CaseRight $params,... 失敗時の処理をするクラスのインスタンス<br>
     * 成功時の値を取って処理するクラスのインスタンス
     * @return mixed
     *
     * @throws \LogicException CaseNone|CaseSomeクラスのインスタンスがパラメータにない場合
     */
    public function match();
}
