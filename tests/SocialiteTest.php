<?php

namespace mosaxiv\Socialite\Tests;

use mosaxiv\Socialite\One\TwitterProvider;
use mosaxiv\Socialite\Socialite;
use PHPUnit\Framework\TestCase;

class SocialiteTest extends TestCase
{
    public function testDriver()
    {
        $config = [
            'client_id' => 'xxxxxxxxxxxxxx',
            'client_secret' => 'xxxxxxxxxxxxx',
            'redirect' => 'http://example.com',
        ];

        $this->assertInstanceOf(TwitterProvider::class, Socialite::driver('twitter', $config));
    }
}
