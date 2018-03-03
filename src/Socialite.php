<?php
namespace mosaxiv\Socialite;

use mosaxiv\Socialite\Contracts\Provider;

/**
 * Class Socialite.
 */
class Socialite
{
    /**
     * @param $driver
     * @param $config
     * @return Provider
     */
    public static function driver($driver, $config)
    {
        $socialiteManager = new SocialiteManager($config);
        return $socialiteManager->driver($driver);
    }
}
