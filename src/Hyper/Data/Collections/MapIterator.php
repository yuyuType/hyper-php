<?php
/**
 * Collection::mapが返すIterator
 *
 * @package Hyper
 * @author yuyuType
 * @link https://github.com/yuyuType/hyper-php
 */

namespace Hyper\Data\Collections;

/**
 * Collection::mapが返すMapIterator
 *
 * @author yuyuType
 */
class MapIterator implements \IteratorAggregate, \ArrayAccess, \Countable
{
    /** 写像関数 */
    private $f;

    /** 写像先のイテレータ */
    private $iterator;

    /**
     * 写像関数と写像先イテレータを受け取る
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
     * @return MapIterator 写像したイテレータ
     */
    public function getIterator()
    {
        foreach ($this->iterator as $value) {
            yield call_user_func($this->f, $value);
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
        return $this->iterator->offsetExists($offset);
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
     * @return $mixed 写像関数の戻り値
     */
    public function offsetGet($offset)
    {
        return call_user_func($this->f, $this->iterator->offsetGet($offset));
    }

    /**
     * Countableインタフェースの実装
     *
     * @return integer 要素の数
     */
    public function count()
    {
        return iterator_count($this->iterator);
    }
}
