<?php

namespace Socialite\One;

use Socialite\ProviderInterface;
use Socialite\SessionTrait;
use Socialite\Util\A;
use Psr\Http\Message\ServerRequestInterface;
use InvalidArgumentException;
use Symfony\Component\HttpFoundation\RedirectResponse as Redirect;
use League\OAuth1\Client\Server\Server;
use League\OAuth1\Client\Credentials\TokenCredentials;
use Laminas\Diactoros\Response\RedirectResponse as psr7Redirect;

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
     * The OAuth server implementation.
     *
     * @var \League\OAuth1\Client\Server\Server
     */
    protected $server;

    /**
     * Create a new provider instance.
     *
     * @param \Psr\Http\Message\ServerRequestInterface $request
     * @param \League\OAuth1\Client\Server\Server $server
     * @param mixed $session
     */
    public function __construct(ServerRequestInterface $request, Server $server, $session)
    {
        $this->setSever($server);
        $this->setRequest($request);
        $this->setSession($session);
    }

    /**
     * @inheritdoc
     */
    public function redirect()
    {
        $temp = $this->server->getTemporaryCredentials();
        $this->setSessionData('Socialite.oauth.temp', $temp);

        return (new Redirect($this->server->getAuthorizationUrl($temp)))->send();
    }

    /**
     * @inheritdoc
     */
    public function psr7Redirect()
    {
        $temp = $this->server->getTemporaryCredentials();
        $this->setSessionData('Socialite.oauth.temp', $temp);

        return new psr7Redirect($this->server->getAuthorizationUrl($temp));
    }

    /**
     * {@inheritdoc}
     */
    public function user()
    {
        if (!$this->hasNecessaryVerifier()) {
            throw new InvalidArgumentException('Invalid request. Missing OAuth verifier.');
        }
        $user = $this->server->getUserDetails($token = $this->getToken());
        $instance = (new User)->setRaw($user->extra)
            ->setToken($token->getIdentifier(), $token->getSecret());
        return $instance->map([
            'id' => $user->uid,
            'nickname' => $user->nickname,
            'name' => $user->name,
            'email' => $user->email,
            'avatar' => $user->imageUrl,
        ]);
    }

    /**
     * Get a Social User instance from a known access token and secret.
     *
     * @param string $token
     * @param string $secret
     * @return \Socialite\One\User
     */
    public function userFromTokenAndSecret($token, $secret)
    {
        $tokenCredentials = new TokenCredentials();
        $tokenCredentials->setIdentifier($token);
        $tokenCredentials->setSecret($secret);
        $user = $this->server->getUserDetails($tokenCredentials);
        $instance = (new User)->setRaw($user->extra)
            ->setToken($tokenCredentials->getIdentifier(), $tokenCredentials->getSecret());
        return $instance->map([
            'id' => $user->uid,
            'nickname' => $user->nickname,
            'name' => $user->name,
            'email' => $user->email,
            'avatar' => $user->imageUrl,
        ]);
    }

    /**
     * Get the token credentials for the request.
     *
     * @return \League\OAuth1\Client\Credentials\TokenCredentials
     */
    protected function getToken()
    {
        $query = $this->request->getQueryParams();
        $temp = $this->getSessionData('Socialite.oauth.temp');
        return $this->server->getTokenCredentials(
            $temp,
            A::get($query, 'oauth_token'),
            A::get($query, 'oauth_verifier')
        );
    }

    /**
     * Determine if the request has the necessary OAuth verifier.
     *
     * @return bool
     */
    protected function hasNecessaryVerifier()
    {
        $query = $this->request->getQueryParams();
        $hasToken = A::get($query, 'oauth_token') !== null;
        $hasVerifier = A::get($query, 'oauth_verifier') !== null;

        return $hasToken && $hasVerifier;
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
     * Set the server instance.
     *
     * @param \League\OAuth1\Client\Server\Server $server
     * @return $this
     */
    public function setSever(Server $server)
    {
        $this->server = $server;
        return $this;
    }
}
