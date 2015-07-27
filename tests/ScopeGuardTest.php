<?php

use Hyper\ScopeGuard;

class ScopeGuardTest extends PHPUnit_Framework_TestCase
{
    public static $stack = [];

    public function testGuard()
    {
        self::$stack = [];
        $this->assertEmpty(self::$stack);
        $guard = ScopeGuard::guard(function () {
            array_push(self::$stack, 'guard is called');
        });
        $this->assertEmpty(self::$stack);
    }

    /**
     * @depends testGuard
     */
    public function testCalledCheck()
    {
        $this->assertNotEmpty(self::$stack);
        $this->assertSame('guard is called', array_pop(self::$stack));
        $this->assertEmpty(self::$stack);
    }

    /**
     * @depends testCalledCheck
     */
    public function testCancel()
    {
        self::$stack = [];
        $this->assertEmpty(self::$stack);
        $guard = ScopeGuard::guard(function () {
            array_push(self::$stack, 'guard is called');
        });
        $this->assertEmpty(self::$stack);
        $guard->cancel();
    }

    /**
     * @depends testCancel
     */
    public function testCancelCheck()
    {
        $this->assertEmpty(self::$stack);
    }

}
