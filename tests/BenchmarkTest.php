<?php

use Hyper\Benchmark;

class BenchmarkTest extends PHPUnit_Framework_TestCase
{
    public function testMeasure()
    {
        $time = Benchmark::measure(function () {
        //    sleep(1);
        });
        //$this->assertSame(1, intval($time));
    }
}
