<?php
/**
 * Either Monad
 *
 * @package Hyper\Data\Eithers
 * @auther yuyuType
 */

namespace Hyper\Data\Eithers;

/**
 * 成功または失敗を意味し、成功時の値をまたは失敗時の値をひとつ持つインタフェース
 */
interface IEither
{
    /**
     * 成功時はその値を、失敗時は$failの戻り値を返す
     *
     * 例)
     * ```php
     * Either::Right(100)->getOrElse(function ($_) { return 0; });
     * ```
     * 結果は100
     * ```php
     * Either::Left('fail')->getOrElse(function ($_) { return 0; });
     * ```
     * 結果は0
     *
     * @param callable $fail fail(mixed $x) : mixed
     * @return mixed $defaultまたは関数 default() : mixedの戻り値
     */
    public function getOrElse(callable $fail);

    /**
     * 成功の場合は自分自身を返す、失敗の場合は引数に取ったEitherを返す
     *
     * 例)
     * ```php
     * Either::Right(100)->orElse(Either::Right(99));
     * ```
     * 結果はRight(100)
     * ```php
     * Either::Left('fail')->orElse(Either::Right(99));
     * ```
     * 結果はRight(99)
     * ```php
     * Either::Left('fail')->orElse(Either::Left('fail2'));
     * ```
     * 結果はLeft('fail2')
     *
     * @param Left|Right|callable $opt Left or Rightのインスタンス、<br>
     * または関数 opt() : Left|Right
     * @return Left|Right
     */
    public function orElse($opt);

    // @codingStandardsIgnoreStart
    /**
     * 成功の場合は自身の値をfに定期用する、失敗時は何もしない
     *
     * 更に解説すると、eachはforeachと同様のことをしているとみなすことが出来る<br>
     * つまりRight(100)をarray(100)と同じとしてみると
     * ```php
     * foreach(array(100) as $x) { print_r($x); } // 100
     * foreach(array() as $x) { print_r($x); } // なにも表示されない
     * ```
     * に対し
     * ```php
     * Either::Right(100)->each('print_r'); // 100
     * Either::Left('fail')->each('print_r'); // なにも表示されない
     * ```
     * と見ることが出来てarrayとRightまたはLeftが置き換わっただけと考えられる
     *
     * 例)
     * ```php
     * Either::Right(100)->each('print_r');
     * ```
     * 100がコンソールに表示される
     * ```php
     * Either::Left('fail')->each('print_r');
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
     * 成功時の値を変換してEitherに包んで返す、失敗時はなにもせず自分自身(Left)を返す
     *
     * 更に解説すると、mapはarray_mapと同様のことをしているとみなすことが出来る<br>
     * つまりRight(3)をarray(3)と同じとしてみると
     * ```php
     * array_map(function ($x) { return $x * 2; } array(3)); // array(6)
     * array_map(function ($x) { return $x * 2; } array()); // array()
     * ```
     * に対し<br>
     * ```php
     * Either::Right(3)->map(function ($x) { return $x * 2; }); // Right(6)
     * Either::Left('fail')->map(function ($x) { return $x * 2; }); // Left('fail')
     * ```
     * と見ることが出来てarrayとRightまたはLeftが置き換わっただけと考えられる
     *
     * 例)
     * ```php
     * Either::Right(3)->map(function ($x) { return $x * 2; });
     * ```
     * 結果はRight(6)
     * ```php
     * Either::Left('fail')->map(function ($x) { return $x * 2; });
     * ```
     * 結果はLeft
     *
     * @param callable $success success(mixed $x) : mixed
     * @return Left|Right
     */
    public function map(callable $success);

    /**
     * 成功時は$successの戻り値を、失敗時は$failを返す
     *
     * ```php
     * Either::Right('test')->map('strtoupper')->getOrElse('Hyper\Func::identity');
     * ```
     * と
     * ```php
     * Either::Right('test')->fold('Hyper\Func::identity', 'strtoupper');
     * ```
     * は同じ
     *
     * 更に解説すると、mapはarray_mapと同様のことをしているとみなすことが出来る<br>
     * つまりRight(100)をarray(100)と同じとしてみると
     * ```php
     * array_reduce(function ($x, $y) { return $x + $y; }, 0, array(100)); // 100
     * array_reduce(function ($x, $y) { return $x + $y; }, 0, array()); // 0
     * ```
     * に対し
     * ```php
     * Either::Right(100)->fold(0, function ($x) { return $x; }); // 100
     * Either::Left('fail')->fold(0, function ($x) { return $x; }); // 0
     * ```
     * と見ることが出来て引数の順番や第二引数に渡す関数のパラメータは１つとなっているが<br>
     * arrayとRightまたはLeftが置き換わっただけと考えられる<br>
     * Eitherはひとつしか値を持たないので第二引数の関数はパラメータを一つしか取らない
     *
     * @param callable $fail fail(mixed $x) : mixed
     * @param callable $success success(mixed $x) : mixed
     * @return mixed
     */
    public function fold(callable $fail, callable $success);

    /**
     * 平坦化
     *
     * Right(Right('hoge'))をRight('hoge')に平坦化して返す<br>
     * Right(Left('fail'))の場合はLeft('fail')を返す<br>
     * Left('fail')の場合もLeft('fail')を返す<br>
     *
     * @return Left|Right
     *
     * @throws \LogicException 保持している値がIEitherのインスタンスではない場合
     */
    public function flatten();

    /**
     * flattenとmapの組み合わせ
     *
     * @param callable $f mixed f(mixed $x) : Left|Right
     * @return Left|Right
     */
    public function flatMap(callable $f);

    /**
     * applicativeスタイル
     *
     * ２つ以上引数を取る関数をmapに渡して残りの引数をapで適用する形のこと
     *
     * 例)
     * ```php
     * $optPattern = Either::Right('/(\w+) (\d+), (\d+)/i');
     * $optReplacement = Either::Right('${1}1,$3');
     * $optString = Either::Right('April 15, 2003');
     * $optPattern->map(Func::curry('preg_replace'))->ap($optReplacement)->ap($optString);
     * ```
     * 結果はRight('April1,2003')
     *
     * Func::curryは引数を１つずつ適用して呼び出すことが出来るようにする関数である
     * mapは引数を１つのみ受け取る関数しか渡せない為一度Func::curryを挟む必要がある
     *
     * apがない場合flatMapとmapを組み合わせて以下の様に書く必要がある
     * ```php
     * $optPattern = Either::Right('/(\w+) (\d+), (\d+)/i');
     * $optReplacement = Either::Right('${1}1,$3');
     * $optString = Either::Right('April 15, 2003');
     * $optPattern->flatMap(function ($pattern) use($optReplacement, $optString) {
     *     return $optReplacement->flatMap(function ($replacement) use($pattern, $optString) {
     *         return $optString->map(function ($string) use($pattern, $replacement) {
     *             return preg_replace($pattern, $replacement, $string);
     *         });
     *     })
     * });
     * ```
     * 結果はRight('April1,2003')
     *
     * これはクロージャが引数の数分だけネストしていて読みづらいのでapを用意する
     *
     * @param Left|Right|callable $opt Left or Rightクラスのインスタンス、<br>
     * または関数 opt() Left|Right
     * @return Left|Right
     */
    public function ap($opt);

    /**
     * 成功または失敗にマッチし、その時に行いたい処理を評価して返す
     *
     * 例)
     * ```php
     * Either::Right(3)->match(
     *     Either::caseLeft('fail'),
     *     Either::caseRight(function ($x) { return $x * 2; })
     * );
     * ```
     * 結果は6
     * ```php
     * Either::Left('fail')->match(
     *     Either::caseLeft('fail2'),
     *     Either::caseRight(function ($x) { return $x * 2; })
     * );
     * ```
     * 結果は'fail2'
     *
     * @param CaseLeft|CaseRight $params,... 失敗時の値を取って処理するクラスのインスタンス<br>
     * 成功時の値を取って処理するクラスのインスタンス
     * @return mixed
     *
     * @throws \LogicException CaseLeft|CaseRightクラスのインスタンスがパラメータにない場合
     */
    public function match();
}
