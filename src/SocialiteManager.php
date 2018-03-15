<?php
namespace mosaxiv\Socialite;

use mosaxiv\Socialite\One\TwitterProvider;
use mosaxiv\Socialite\Two\BitbucketProvider;
use mosaxiv\Socialite\Two\FacebookProvider;
use mosaxiv\Socialite\Two\GithubProvider;
use mosaxiv\Socialite\Two\GoogleProvider;
use mosaxiv\Socialite\Two\LinkedInProvider;
use Symfony\Component\HttpFoundation\Session\Session;
use League\OAuth1\Client\Server\Twitter as TwitterServer;
use Zend\Diactoros\ServerRequestFactory;

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
     * @var \Psr\Http\Message\ServerRequestInterface
     */
    protected $request;

    /**
     * The session instance.
     *
     * @var mixed
     */
    protected $session;

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
     * @return ProviderInterface
     */
    public function driver(string $driver)
    {
        if (!isset($this->drivers[$driver])) {
            throw new \InvalidArgumentException("Driver not supported.");
        }

        $provider = $driver . 'Provider';
        if (method_exists($this, $provider)) {
            return $this->$provider();
        }

        return new $this->drivers[$driver](
            $this->getRequest(),
            $this->config,
            $this->getSession()
        );
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
            new TwitterServer($this->formatConfig()),
            $this->getSession()
        );
    }

    /**
     * @return \Psr\Http\Message\ServerRequestInterface
     */
    protected function getRequest()
    {
        return $this->request ?: ServerRequestFactory::fromGlobals();
    }

    /**
     * @return mixed
     */
    protected function getSession()
    {
        return $this->session ?: new Session();
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
