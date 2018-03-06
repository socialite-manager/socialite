<?php
namespace mosaxiv\Socialite;

use mosaxiv\Socialite\Contracts\Provider;

class Socialite
{
    /**
     * @param string $driver
     * @param array $config
     * @return Provider
     */
    public static function driver(string $driver, array $config)
    {
        $socialiteManager = new SocialiteManager($config);
        return $socialiteManager->driver($driver);
    }
}
