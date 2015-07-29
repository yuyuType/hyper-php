<?php
/**
 * Collection::initが返すIterator
 *
 * @package Hyper
 * @author yuyuType
 * @link https://github.com/yuyuType/hyper-php
 */

namespace Hyper\Data\Collections;

/**
 * Collection::initが返すInitIterator
 *
 * @author yuyuType
 */
class InitIterator implements \IteratorAggregate
{
    /** 写像先のイテレータ */
    private $iterator;

    /**
     * 写像関数と写像先イテレータを受け取る
     *
     * @param Traversable $iterator 写像先イテレータ
     * @SuppressWarnings(PHPMD.ShortVariable)
     */
    public function __construct(\Traversable $iterator)
    {
        $this->iterator = $iterator;
    }

    /**
     * IteratorAggregateインタフェースの実装
     *
     * @return InitIterator 写像したイテレータ
     */
    public function getIterator()
    {
        $queue = new \SplQueue();
        while ($this->iterator->valid()) {
            $queue->enqueue([$this->iterator->key(), $this->iterator->current()]);
            $this->iterator->next();
            break;
        }
        while ($this->iterator->valid()) {
            $queue->enqueue([$this->iterator->key(), $this->iterator->current()]);
            $keyVal = $queue->dequeue();
            yield $keyVal[0] => $keyVal[1];
            $this->iterator->next();
        }
    }
}
