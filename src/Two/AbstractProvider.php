<?php

namespace Socialite\Two;

use GuzzleHttp\Client;
use Socialite\ProviderInterface;
use Socialite\SessionTrait;
use Socialite\Util\A;
use Psr\Http\Message\ServerRequestInterface;
use Symfony\Component\HttpFoundation\RedirectResponse as Redirect;
use Zend\Diactoros\Response\RedirectResponse as psr7Redirect;

abstract class AbstractProvider implements ProviderInterface
{
    use SessionTrait;

    /**
     * The HTTP request instance.
     *
     * @var \Psr\Http\Message\ServerRequestInterface
     */
    protected $request;

    /**
     * The HTTP Client instance.
     *
     * @var null|\GuzzleHttp\Client
     */
    protected $httpClient;

    /**
     * The client ID.
     *
     * @var string
     */
    protected $clientId;

    /**
     * The client secret.
     *
     * @var string
     */
    protected $clientSecret;

    /**
     * The redirect URL.
     *
     * @var string
     */
    protected $redirectUrl;

    /**
     * The custom parameters to be sent with the request.
     *
     * @var array
     */
    protected $parameters = [];

    /**
     * The scopes being requested.
     *
     * @var array
     */
    protected $scopes = [];

    /**
     * The separating character for the requested scopes.
     *
     * @var string
     */
    protected $scopeSeparator = ',';

    /**
     * The type of the encoding in the query.
     *
     * @var int Can be either PHP_QUERY_RFC3986 or PHP_QUERY_RFC1738.
     */
    protected $encodingType = PHP_QUERY_RFC1738;

    /**
     * Indicates if the session state should be utilized.
     *
     * @var bool
     */
    protected $stateless = false;

    /**
     * The custom Guzzle configuration options.
     *
     * @var array
     */
    protected $guzzle = [];

    /**
     * Create a new provider instance.
     *
     * @param \Psr\Http\Message\ServerRequestInterface $request
     * @param array $config
     * @param array $guzzle
     */
    public function __construct(ServerRequestInterface $request, array $config, $session, array $guzzle = [])
    {
        $this->guzzle = $guzzle;
        $this->setRequest($request);
        $this->setSession($session);
        $this->clientId = $config['client_id'];
        $this->redirectUrl = $config['redirect'];
        $this->clientSecret = $config['client_secret'];
    }

    /**
     * Get the authentication URL for the provider.
     *
     * @param string $state
     * @return string
     */
    abstract protected function getAuthUrl(string $state);

    /**
     * Get the token URL for the provider.
     *
     * @return string
     */
    abstract protected function getTokenUrl();

    /**
     * Get the raw user for the given access token.
     *
     * @param string $token
     * @return array
     */
    abstract protected function getUserByToken(string $token);

    /**
     * Map the raw user array to a Socialite User instance.
     *
     * @param array $user
     * @return \Socialite\Two\User
     */
    abstract protected function mapUserToObject(array $user);

    /**
     * {@inheritdoc}
     */
    public function redirect()
    {
        $state = null;
        if ($this->usesState()) {
            $state = bin2hex(random_bytes(32));
            $this->setSessionData('Socialite.state', $state);
        }
        return (new Redirect($this->getAuthUrl($state)))->send();
    }

    /**
     * {@inheritdoc}
     */
    public function psr7Redirect()
    {
        $state = null;
        if ($this->usesState()) {
            $state = bin2hex(random_bytes(32));
            $this->setSessionData('Socialite.state', $state);
        }

        return new psr7Redirect($this->getAuthUrl($state));
    }

    /**
     * Get the authentication URL for the provider.
     *
     * @param string $url
     * @param string $state
     * @return string
     */
    protected function buildAuthUrlFromBase(string $url, string $state)
    {
        return $url . '?' . http_build_query($this->getCodeFields($state), '', '&', $this->encodingType);
    }

    /**
     * Get the GET parameters for the code request.
     *
     * @param string|null $state
     * @return array
     */
    protected function getCodeFields($state = null)
    {
        $fields = [
            'client_id' => $this->clientId,
            'redirect_uri' => $this->redirectUrl,
            'scope' => $this->formatScopes($this->getScopes(), $this->scopeSeparator),
            'response_type' => 'code',
        ];
        if ($this->usesState()) {
            $fields['state'] = $state;
        }
        return array_merge($fields, $this->parameters);
    }

    /**
     * Format the given scopes.
     *
     * @param array $scopes
     * @param string $scopeSeparator
     * @return string
     */
    protected function formatScopes(array $scopes, $scopeSeparator)
    {
        return implode($scopeSeparator, $scopes);
    }

    /**
     * {@inheritdoc}
     */
    public function user()
    {
        if ($this->hasInvalidState()) {
            throw new InvalidStateException;
        }
        $response = $this->getAccessTokenResponse($this->getCode());
        $token = $response['access_token'] ?? null;
        $user = $this->mapUserToObject($this->getUserByToken($token));
        return $user->setToken($token)
            ->setRefreshToken($response['refresh_token'] ?? null)
            ->setExpiresIn($response['expires_in'] ?? null);
    }

    /**
     * Get a Social User instance from a known access token.
     *
     * @param string $token
     * @return \Socialite\Two\User
     */
    public function userFromToken(string $token)
    {
        $user = $this->mapUserToObject($this->getUserByToken($token));
        return $user->setToken($token);
    }

    /**
     * Determine if the current request / session has a mismatching "state".
     *
     * @return bool
     */
    protected function hasInvalidState()
    {
        if ($this->isStateless()) {
            return false;
        }
        $state = $this->getSessionData('Socialite.state');
        return !(strlen($state) > 0 && A::get($this->request->getQueryParams(), 'state') === $state);
    }

    /**
     * Get the access token response for the given code.
     *
     * @param string $code
     * @return array
     */
    public function getAccessTokenResponse(string $code)
    {
        $response = $this->getHttpClient()->post($this->getTokenUrl(), [
            'headers' => ['Accept' => 'application/json'],
            'form_params' => $this->getTokenFields($code),
        ]);
        return json_decode($response->getBody(), true);
    }

    /**
     * Get the POST fields for the token request.
     *
     * @param string $code
     * @return array
     */
    protected function getTokenFields(string $code)
    {
        return [
            'client_id' => $this->clientId,
            'client_secret' => $this->clientSecret,
            'code' => $code,
            'redirect_uri' => $this->redirectUrl,
        ];
    }

    /**
     * Get the code from the request.
     *
     * @return string
     */
    protected function getCode()
    {
        return A::get($this->request->getQueryParams(), 'code');
    }

    /**
     * Merge the scopes of the requested access.
     *
     * @param array|string $scopes
     * @return $this
     */
    public function scopes($scopes)
    {
        $this->scopes = array_unique(array_merge($this->scopes, (array)$scopes));
        return $this;
    }

    /**
     * Set the scopes of the requested access.
     *
     * @param array|string $scopes
     * @return $this
     */
    public function setScopes($scopes)
    {
        $this->scopes = array_unique((array)$scopes);
        return $this;
    }

    /**
     * Get the current scopes.
     *
     * @return array
     */
    public function getScopes()
    {
        return $this->scopes;
    }

    /**
     * Set the redirect URL.
     *
     * @param string $url
     * @return $this
     */
    public function redirectUrl(string $url)
    {
        $this->redirectUrl = $url;
        return $this;
    }

    /**
     * Get a instance of the Guzzle HTTP client.
     *
     * @return \GuzzleHttp\Client
     */
    protected function getHttpClient()
    {
        if (is_null($this->httpClient)) {
            $this->httpClient = new Client($this->guzzle);
        }
        return $this->httpClient;
    }

    /**
     * Set the Guzzle HTTP client instance.
     *
     * @param \GuzzleHttp\Client $client
     * @return $this
     */
    public function setHttpClient(Client $client)
    {
        $this->httpClient = $client;
        return $this;
    }

    /**
     * Set the request instance.
     *
     * @param \Psr\Http\Message\ServerRequestInterface $request
     * @return $this
     */
    public function setRequest(ServerRequestInterface $request)
    {
        $this->request = $request;
        return $this;
    }

    /**
     * Determine if the provider is operating with state.
     *
     * @return bool
     */
    protected function usesState()
    {
        return !$this->stateless;
    }

    /**
     * Determine if the provider is operating as stateless.
     *
     * @return bool
     */
    protected function isStateless()
    {
        return $this->stateless;
    }

    /**
     * Indicates that the provider should operate as stateless.
     *
     * @return $this
     */
    public function stateless()
    {
        $this->stateless = true;
        return $this;
    }

    /**
     * Set the custom parameters of the request.
     *
     * @param array $parameters
     * @return $this
     */
    public function with(array $parameters)
    {
        $this->parameters = $parameters;
        return $this;
    }
}
