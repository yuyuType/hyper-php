<?php
/**
 * Collection::filterが返すIterator
 *
 * @package Hyper
 * @author yuyuType
 * @link https://github.com/yuyuType/hyper-php
 */

namespace Hyper\Data\Collections;

/**
 * Collection::filterが返すFilterdIterator
 *
 * @author yuyuType
 */
class FilterdIterator implements \IteratorAggregate, \ArrayAccess, \Countable
{
    /** 条件関数 */
    private $f;

    /** イテレータ */
    private $iterator;

    /**
     * 条件関数とイテレータを受け取る
     *
     * @param callable $f mixed f(mixed $x)
     * @param Traversable $iterator 写像先イテレータ
     * @SuppressWarnings(PHPMD.ShortVariable)
     */
    public function __construct(callable $f, \Traversable $iterator)
    {
        $this->f = $f;
        $this->iterator = $iterator;
    }

    /**
     * IteratorAggregateインタフェースの実装
     *
     * @return FilterdIterator フィルタしたイテレータ
     */
    public function getIterator()
    {
        foreach ($this->iterator as $value) {
            if (call_user_func($this->f, $value)) {
                yield $value;
            }
        }
    }

    /**
     * ArrayAccessインタフェースの実装
     *
     * @throws \LogicException 変更不可
     */
    public function offsetSet($offset, $value)
    {
        throw new \LogicException("This is an immutable object.");
    }

    /**
     * ArrayAccessインタフェースの実装
     *
     * @return bool 値の存在確認
     */
    public function offsetExists($offset)
    {
        $count = 0;
        foreach ($this->iterator as $value) {
            if (call_user_func($this->f, $value)) {
                if ($count++ === $offset) {
                    return true;
                }
            }
        }
        return false;
    }

    /**
     * ArrayAccessインタフェースの実装
     *
     * @throws \LogicException 変更不可
     */
    public function offsetUnset($offset)
    {
        throw new \LogicException("This is an immutable object.");
    }

    /**
     * ArrayAccessインタフェースの実装
     *
     * @return mixed 写像関数の戻り値
     */
    public function offsetGet($offset)
    {
        $count = 0;
        foreach ($this->iterator as $value) {
            if (call_user_func($this->f, $value)) {
                if ($count++ === $offset) {
                    return $value;
                }
            }
        }
        return null;
    }

    /**
     * Countableインタフェースの実装
     *
     * @return integer 要素の数
     */
    public function count()
    {
        $count = 0;
        foreach ($this->iterator as $value) {
            if (call_user_func($this->f, $value)) {
                ++$count;
            }
        }
        return $count;
    }
}
