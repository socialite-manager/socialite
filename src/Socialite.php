<?php
namespace mosaxiv\Socialite;

use mosaxiv\Socialite\Contracts\Provider;
use mosaxiv\Socialite\One\TwitterProvider;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\Session;
use League\OAuth1\Client\Server\Twitter;

/**
 * Class SocialiteManager.
 */
class Socialite
{
    /**
     * The drivers.
     *
     * @var array
     */
    protected static $drivers = [
        'twitter' => TwitterProvider::class,
    ];

    /**
     * Get a driver instance.
     *
     * @param string $driver
     * @param array $config
     *
     * @return Provider
     */
    public static function driver($driver, $config)
    {
        if (isset(static::$drivers[$driver])) {
            $provider = static::$drivers[$driver];
            return new $provider(static::createRequest(), new Twitter($config));
        }

        throw new \InvalidArgumentException("Driver not supported.");
    }

    /**
     * Create request instance.
     *
     * @return Request
     */
    protected static function createRequest()
    {
        $request = Request::createFromGlobals();
        $request->setSession(new Session());
        return $request;
    }
}
