<?php
/**
 * 関数に対する操作関数群
 *
 * @package Hyper
 * @author yuyuType
 * @link https://github.com/yuyuType/hyper-php
 */

namespace Hyper;

/**
 * 関数に対する操作関数群
 *
 * @author yuyuType
 */
class Func
{
    /**
     * 引数$fを評価してその結果を返す
     *
     * @param mixed $f 型mixed f()の関数、または値
     * @return mixed $fが関数の場合その戻り値、値の場合$f自身
     * @SuppressWarnings(PHPMD.ShortVariable)
     */
    public static function evaluate($f)
    {
        if (is_callable($f)) {
            return call_user_func($f);
        }
        return $f;
    }

    /**
     * 関数$fに引数を適用した新しい関数を返す
     *
     * 与えられた引数の数が必要十分であっても$fは呼び出さない
     *
     * 例)
     *
     * ``` php
     * $bindFunc = Func::bind(function ($x, $y, $z) { return $x + $y + $z; }, 1, 2);
     * $sum = $bindFunc(3); // 1 + 2 + 3 = 6
     * ```
     *
     * このように一度に３つ引数を受け取って処理する関数
     * function ($x, $y, $z) { return $x + $y + $z; });を
     * bind関数により$xに1を、$yに2を適用した新しい関数を作成して返す
     * 下記curry関数に似ているがこちらはbind時にすべての引数を適用しても
     * 対象の関数が呼び出されないことに注意
     *
     * ``` php
     * $bindFunc = Func::bind(function ($x, $y, $z) { return $x + $y + $z; }, 1, 2, 3);
     * $sum = $bindFunc(); // 1 + 2 + 3 = 6
     * ```
     *
     * すべての引数を適用した引数なしの新しい関数が返される
     * 実行するには引数なし関数呼び出しが必要
     *
     * @param callable $f mixed f([mixed $...])
     * @param mixed $params,... $fに与えるパラメータの一部
     * @return \Clojuer Object
     * @SuppressWarnings(PHPMD.ShortVariable)
     */
    public static function bind(callable $f)
    {
        $args = func_get_args();
        $f = array_shift($args);

        return function () use ($f, $args) {
            return call_user_func_array($f, array_merge($args, func_get_args()));
        };
    }

    /**
     * カリー化
     *
     * カリー化とは複数の引数を取る関数を、引数を１つずつ適用して
     * すべての引数が適用されたらその関数が呼び出されるように変換することを言う
     *
     * 例)
     *
     * ``` php
     * $curried = Func::curry(function ($x, $y, $z) { return $x + $y + $z; });
     * $applyOne = $curried(1);
     * $applyTow = $applyOne(2);
     * $sum = $applyTow(3); // 1 + 2 + 3 = 6
     * ```
     *
     * このように一度に３つ引数を受け取って処理する関数
     * function ($x, $y, $z) { return $x + $y + $z; });を
     * １つずつ引数を受け取ってすべての引数が揃った時点で呼び出される形に変換する関数である
     *
     * @param callable $f mixed f([mixed $...])
     * @return \Clojuer Object
     * @SuppressWarnings(PHPMD.ShortVariable)
     */
    public static function curry(callable $f)
    {
        if (is_string($f)) {
            // callableな$fが文字列でくる場合はstatic methodまたはfunctionの時しかない
            if (preg_match('/^([\a-zA-Z0-9_]+)::([a-zA-Z0-9_]+)$/', $f, $matches) === 1) {
                // \はnamespaceを考慮
                // static method
                $refMethod = new \ReflectionMethod($matches[1], $matches[2]);
                return self::__curryMethod($refMethod, $matches[1]);
            }
            $refFunc = new \ReflectionFunction($f);
            return self::__curryFunction($refFunc);
        } elseif (is_array($f) && count($f) === 2) {
            $refMethod = new \ReflectionMethod($f[0], $f[1]);
            return self::__curryMethod($refMethod, $f[0]);
        }
        // Clojuer
        $refFunc = new \ReflectionFunction($f);
        return self::__curryFunction($refFunc);
    }

    /**
     * クラス・メソッドのカリー化
     *
     * @param \ReflectionMethod $method メソッド本体
     * @param mixed $object オブジェクト型またはstring
     * @return \Clojuer Object
     * @SuppressWarnings(PHPMD.ShortVariable)
     */
    private static function __curryMethod(\ReflectionMethod $method, $object)
    {
        // 引数なしメソッドに対応
        if ($method->getNumberOfRequiredParameters() === 0) {
            return function () use ($method, $object) {
                return $method->isStatic()
                    ? $method->invoke(null)
                    : $method->invoke(is_object($object) ? $object : new $method->class);
            };
        }

        $args = func_get_args();
        $method = array_shift($args);
        $object = array_shift($args);
        return function () use ($method, $object, $args) {
            $myargs = func_get_args();
            $parameters = array_merge($args, $myargs);
            if (count($parameters) >= $method->getNumberOfRequiredParameters()) {
                return $method->isStatic()
                    ? $method->invokeArgs(null, $parameters)
                    : $method->invokeArgs(is_object($object) ? $object : new $method->class, $parameters);
            }
            return call_user_func_array('self::__curryMethod', array_merge([$method, $object], $parameters));
        };
    }

    /**
     * 通常関数のカリー化
     *
     * @param \ReflectionFunction $function 関数本体
     * @return \Clojuer Object
     * @SuppressWarnings(PHPMD.ShortVariable)
     */
    private static function __curryFunction(\ReflectionFunction $function)
    {
        // 引数なし関数に対応
        if ($function->getNumberOfRequiredParameters() === 0) {
            return function () use ($function) {
                return $function->invoke();
            };
        }

        $args = func_get_args();
        $function = array_shift($args);
        return function () use ($args, $function) {
            $myargs = func_get_args();
            $parameters = array_merge($args, $myargs);
            if (count($parameters) >= $function->getNumberOfRequiredParameters()) {
                return $function->invokeArgs($parameters);
            }
            return call_user_func_array('self::__curryFunction', array_merge([$function], $parameters));
        };
    }

    /**
     * 関数合成
     *
     * f(g(x)) == compose($f, $g)(x)
     *
     * @param mixed $f mixed f(mixed $x)
     * @param mixed $g mixed g(mixed $y)
     * @return \Clojuer Object
     * @SuppressWarnings(PHPMD.ShortVariable)
     */
    public static function compose($f, $g)
    {
        return function ($x) use ($f, $g) {
            return call_user_func($f, call_user_func($g, $x));
        };
    }

    /**
     * flip
     *
     * f(x, y) == flip("f")(y, x)
     *
     * @param callable $f mixed f(mixed $x, mixed $y)
     * @return \Clojuer Object mixed f(mixed $y, mixed $x)
     * @SuppressWarnings(PHPMD.ShortVariable)
     */
    public static function flip(callable $f)
    {
        return self::curry(function ($x, $y) use ($f) {
            return call_user_func($f, $y, $x);
        });
    }

    /**
     * 引数をそのまま返す
     *
     * @param mixed $x この値がそのまま返される
     * @return mixed
     * @SuppressWarnings(PHPMD.ShortVariable)
     */
    public static function identity($x)
    {
        return $x;
    }
}
