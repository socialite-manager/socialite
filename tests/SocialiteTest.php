<?php

namespace mosaxiv\Socialite\Tests;

use mosaxiv\Socialite\One\TwitterProvider;
use mosaxiv\Socialite\Socialite;
use mosaxiv\Socialite\Two\GithubProvider;
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
        $this->assertInstanceOf(GithubProvider::class, Socialite::driver('github', $config));
    }

    public function testDriverError()
    {
        $this->expectException(\InvalidArgumentException::class);
        Socialite::driver('twitter', []);
    }
}
