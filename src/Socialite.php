<?php
namespace Socialite;

class Socialite
{
    /**
     * @param string $driver
     * @param array $config
     * @return \Socialite\One\AbstractProvider|\Socialite\Two\AbstractProvider
     */
    public static function driver(string $driver, array $config)
    {
        $socialiteManager = new SocialiteManager($config);
        return $socialiteManager->driver($driver);
    }
}
