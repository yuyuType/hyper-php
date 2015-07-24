<?php

use Hyper\ScopeGuard;

class ScopeGuardTest extends PHPUnit_Framework_TestCase
{
    public static $stack = [];

    public function testScopeGuard()
    {
        self::$stack = [];
        $this->assertEmpty(self::$stack);
        $guard = ScopeGuard::guard(function () {
            array_push(self::$stack, 'guard is called');
        });
        $this->assertEmpty(self::$stack);
    }

    /**
     * @depends testScopeGuard
     */
    public function testScopeGuardCalledCheck()
    {
        $this->assertNotEmpty(self::$stack);
        $this->assertSame('guard is called', array_pop(self::$stack));
        $this->assertEmpty(self::$stack);
    }

    /**
     * @depends testScopeGuardCalledCheck
     */
    public function testScopeGuardCancel()
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
     * @depends testScopeGuardCancel
     */
    public function testScopeGuardCancelCheck()
    {
        $this->assertEmpty(self::$stack);
    }

}
