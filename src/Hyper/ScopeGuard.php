<?php
/**
 * スコープの終了時に呼ばれる処理を実行する
 *
 * @package Hyper
 * @author yuyuType
 * @link https://github.com/yuyuType/hyper-php
 */

namespace Hyper;

/**
 * スコープの終了時に呼ばれる処理を実行するクラス
 *
 * PHPのスコープは少し特殊なので注意PHPのスコープの仕様に関しては
 * {@link http://php.net/manual/ja/language.variables.scope.php 変数のスコープ}を参照
 *
 * @author yuyuType
 */
class ScopeGuard
{
    /** @var callable $exitAction スコープ終了時に呼ばれる関数 */
    private $exitAction;
    /** @var bool $isCancel スコープ終了時に呼ぶ処理をキャンセルするかを決めるフラグ */
    private $isCancel = false;

    /**
     * スコープ終了時に呼ばれる関数を受け取る
     *
     * インスタンスの生成は{@link \Hyper\ScopeGuard::guard(callable)}を使う
     *
     * @param callable $exitAction exitAction() : void
     * @return void
     */
    private function __construct(callable $exitAction)
    {
        $this->exitAction = $exitAction;
    }

    /**
     * スコープ終了時に呼ばれる関数を実行する
     *
     * @return void
     */
    public function __destruct()
    {
        if (!$this->isCancel) {
            call_user_func($this->exitAction);
        }
    }

    /**
     * スコープ終了時に呼ばれる関数の実行をキャンセルする
     *
     * 例)
     * ```php
     * use Hyper\ScopeGuard;
     *
     * function scopeGuardCancelTest() {
     *     $guard = ScopeGuard::guard(function () {
     *         echo 'スコープ終了時に行う処理';
     *     });
     *     $guard->cancel();
     * } // なにも実行されない
     * ```
     *
     * @return void
     */
    public function cancel()
    {
        $this->isCancel = true;
    }

    /**
     * スコープ終了時に処理するクラスのインスタンスを返す
     *
     * 例)
     * ```php
     * use Hyper\ScopeGuard;
     *
     * function scopeGuardTest() {
     *     $guard = ScopeGuard::guard(function () {
     *         echo 'スコープ終了時に行う処理';
     *     });
     * } // ココで'スコープ終了時に行う処理'が表示される
     * ```
     *
     * @param callable $exitAction exitAction() : void
     * @return ScopeGuard
     */
    public static function guard(callable $exitAction)
    {
        return new self($exitAction);
    }
}
