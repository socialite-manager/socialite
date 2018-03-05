<?php

namespace mosaxiv\Socialite\Tests;

use mosaxiv\Socialite\One\TwitterProvider;
use mosaxiv\Socialite\Socialite;
use mosaxiv\Socialite\Two\BitbucketProvider;
use mosaxiv\Socialite\Two\FacebookProvider;
use mosaxiv\Socialite\Two\GithubProvider;
use mosaxiv\Socialite\Two\GoogleProvider;
use mosaxiv\Socialite\Two\LinkedInProvider;
use PHPUnit\Framework\TestCase;

class SocialiteTest extends TestCase
{
    protected $config = [
        'client_id' => 'xxxxxxxxxxxxxx',
        'client_secret' => 'xxxxxxxxxxxxx',
        'redirect' => 'http://example.com',
    ];

    public function testDriver()
    {
        $this->assertInstanceOf(TwitterProvider::class, Socialite::driver('twitter', $this->config));
        $this->assertInstanceOf(GithubProvider::class, Socialite::driver('github', $this->config));
        $this->assertInstanceOf(GoogleProvider::class, Socialite::driver('google', $this->config));
        $this->assertInstanceOf(FacebookProvider::class, Socialite::driver('facebook', $this->config));
        $this->assertInstanceOf(BitbucketProvider::class, Socialite::driver('bitbucket', $this->config));
        $this->assertInstanceOf(LinkedInProvider::class, Socialite::driver('linkedin', $this->config));
    }

    public function testDriverError()
    {
        $this->expectException(\InvalidArgumentException::class);
        Socialite::driver('test', $this->config);
    }

    public function testDriverConfigError()
    {
        $this->expectException(\InvalidArgumentException::class);
        Socialite::driver('twitter', []);
    }
}
