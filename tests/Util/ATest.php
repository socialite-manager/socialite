<?php

namespace mosaxiv\Socialite\Tests\Util;

use PHPUnit\Framework\TestCase;
use mosaxiv\Socialite\Util\A;

class ATest extends TestCase
{
    public function testGet()
    {
        $array = [
            'User' => [
                'id' => 2,
                'group_id' => 1,
                'Data' => [
                    'user' => 'test1',
                    'name' => 'test2'
                ]
            ]
        ];

        $this->assertSame($array['User'], A::get($array, 'User'));
        $this->assertSame(2, A::get($array, 'User.id'));
        $this->assertSame('test1', A::get($array, 'User.Data.user'));
        $this->assertNull(A::get($array, 'User.Data.test'));
    }
}
