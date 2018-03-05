<?php
namespace mosaxiv\Socialite;

use mosaxiv\Socialite\Contracts\Provider;
use mosaxiv\Socialite\One\TwitterProvider;
use mosaxiv\Socialite\Two\BitbucketProvider;
use mosaxiv\Socialite\Two\FacebookProvider;
use mosaxiv\Socialite\Two\GithubProvider;
use mosaxiv\Socialite\Two\GoogleProvider;
use mosaxiv\Socialite\Two\LinkedInProvider;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\Session;
use League\OAuth1\Client\Server\Twitter as TwitterServer;

/**
 * Class SocialiteManager.
 */
class SocialiteManager
{
    /**
     * The configuration.
     *
     * @var array
     */
    protected $config = [];

    /**
     * The request instance.
     *
     * @var Request
     */
    protected $request;

    /**
     * The drivers.
     *
     * @var array
     */
    protected $drivers = [
        'twitter' => TwitterProvider::class,
        'github' => GithubProvider::class,
        'google' => GoogleProvider::class,
        'facebook' => FacebookProvider::class,
        'bitbucket' => BitbucketProvider::class,
        'linkedin' => LinkedInProvider::class,
    ];

    /**
     * SocialiteManager constructor.
     *
     * @param array $config
     */
    public function __construct(array $config)
    {
        if (!isset($config['client_id'], $config['redirect'], $config['client_secret'])) {
            throw new \InvalidArgumentException('client_id/redirect/client_secret is required');
        }

        $this->config = $config;
    }

    /**
     * Get a driver instance.
     *
     * @param string $driver
     *
     * @return Provider
     */
    public function driver($driver)
    {
        if (!isset($this->drivers[$driver])) {
            throw new \InvalidArgumentException("Driver not supported.");
        }

        $provider = $driver . 'Provider';
        if (method_exists($this, $provider)) {
            return $this->$provider();
        }

        return new $this->drivers[$driver]($this->getRequest(), $this->config);
    }

    /**
     * Create an instance of the specified driver.
     *
     * @return \mosaxiv\Socialite\One\AbstractProvider
     */
    protected function twitterProvider()
    {
        return new TwitterProvider(
            $this->getRequest(),
            new TwitterServer($this->formatConfig())
        );
    }

    /**
     * @return \Symfony\Component\HttpFoundation\Request
     */
    protected function getRequest()
    {
        return $this->request ?: $this->createRequest();
    }

    /**
     * Create request instance.
     *
     * @return Request
     */
    protected function createRequest()
    {
        $request = Request::createFromGlobals();
        $session = new Session();
        $request->setSession($session);

        return $request;
    }

    /**
     * Format the server configuration.
     *
     * @return array
     */
    protected function formatConfig()
    {
        return array_merge([
            'identifier' => $this->config['client_id'],
            'secret' => $this->config['client_secret'],
            'callback_uri' => $this->config['redirect'],
        ], $this->config);
    }
}
