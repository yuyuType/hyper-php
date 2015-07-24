<?php

use Hyper\PipelineStream;

class PipelineStreamTest extends PHPUnit_Framework_TestCase
{
    public function testPipelineStreamBind()
    {
        $stream = new PipelineStream(range(1, 5));
        $result = $stream->bind('array_map', function ($x) { return $x * 2; })
            ->get();
        $this->assertSame([2, 4, 6, 8, 10], $result);
    }
}
