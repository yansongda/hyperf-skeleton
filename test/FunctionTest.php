<?php

namespace App\Test;

use App\Constants\RequestConstant;
use App\Util\Context;

class FunctionTest extends TestCase
{
    public function testGetRequestId()
    {
        Context::set(RequestConstant::HEADER_REQUEST_ID, 'yansongda');

        self::assertEquals('yansongda', get_request_id());
    }

    public function testIsInternalHost()
    {
        self::assertTrue(is_internal_host('127.0.0.1'));
        self::assertTrue(is_internal_host('127.0.0.1:8080'));
        self::assertTrue(is_internal_host('192.168.0.1'));
        self::assertTrue(is_internal_host('192.168.0.1:8080'));
        self::assertTrue(is_internal_host('172.16.0.1'));
        self::assertTrue(is_internal_host('172.16.0.1:8080'));
        self::assertTrue(is_internal_host('10.0.0.1'));
        self::assertTrue(is_internal_host('10.0.0.1:8080'));

        self::assertFalse(is_internal_host('1.1.1.1'));
        self::assertFalse(is_internal_host('1.1.1.1:8080'));
    }

    public function testToArray()
    {
        self::assertEquals(['a', 'b'], to_array('a,b'));
        self::assertEquals(['a', 'b'], to_array('a，b'));
        self::assertEquals(['a', 'b'], to_array(['a', 'b']));
        self::assertEquals([], to_array(''));
        self::assertEquals([], to_array(null));
    }

    public function testToBool()
    {
        self::assertTrue(to_bool('true'));
        self::assertFalse(to_bool('false'));
        self::assertTrue(to_bool(true));
        self::assertFalse(to_bool(false));
        self::assertFalse(to_bool(null));
    }

}
