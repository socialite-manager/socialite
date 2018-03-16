<?php
namespace mosaxiv\Socialite;

class Socialite
{
    /**
     * @param string $driver
     * @param array $config
     * @return \mosaxiv\Socialite\One\AbstractProvider|\mosaxiv\Socialite\Two\AbstractProvider
     */
    public static function driver(string $driver, array $config)
    {
        $socialiteManager = new SocialiteManager($config);
        return $socialiteManager->driver($driver);
    }
}
