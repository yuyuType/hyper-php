<?php

use Hyper\Data\Option;
use Hyper\StringUtil;

class StringUtilTest extends PHPUnit_Framework_TestCase
{
    public function testIsDigit()
    {
        $this->assertSame(true, StringUtil::isDigit('12345'));
        $this->assertSame(false, StringUtil::isDigit('0x12345'));
        $this->assertSame(false, StringUtil::isDigit('abcdef'));
        $this->assertSame(true, StringUtil::isDigit(234124));
        $this->assertSame(true, StringUtil::isDigit(255));
        $this->assertSame(true, StringUtil::isDigit('255'));
        $this->assertSame(false, StringUtil::isDigit(true));
    }
}
