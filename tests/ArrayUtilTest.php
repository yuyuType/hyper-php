<?php

use Hyper\Data\Option;
use Hyper\ArrayUtil;

class ArrayUtilTest extends PHPUnit_Framework_TestCase
{
    public function testLookup()
    {
        $arr = ['val' => 1, 'val1' => 2];
        $this->assertEquals(Option::Some(2), ArrayUtil::lookup('val1', $arr));
        $this->assertEquals(Option::Some(1), ArrayUtil::lookup('val', $arr));
        $this->assertEquals(Option::None(), ArrayUtil::lookup('none', $arr));
    }
}
