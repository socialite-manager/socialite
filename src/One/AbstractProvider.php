<?php
namespace mosaxiv\Socialite\One;

use Symfony\Component\HttpFoundation\Request;
use InvalidArgumentException;
use Symfony\Component\HttpFoundation\RedirectResponse;
use League\OAuth1\Client\Server\Server;
use League\OAuth1\Client\Credentials\TokenCredentials;
use mosaxiv\Socialite\Contracts\Provider as ProviderContract;

abstract class AbstractProvider implements ProviderContract
{
    /**
     * The HTTP request instance.
     *
     * @var \Symfony\Component\HttpFoundation\Request
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
     * @param  \Symfony\Component\HttpFoundation\Request $request
     * @param  \League\OAuth1\Client\Server\Server $server
     */
    public function __construct(Request $request, Server $server)
    {
        $this->server = $server;
        $this->request = $request;
    }

    /**
     * Redirect the user to the authentication page for the provider.
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function redirect()
    {
        $this->request->getSession()->set(
            'oauth.temp',
            $temp = $this->server->getTemporaryCredentials()
        );
        return (new RedirectResponse($this->server->getAuthorizationUrl($temp)))->send();
    }

    /**
     * Get the User instance for the authenticated user.
     *
     * @throws \InvalidArgumentException
     * @return \mosaxiv\Socialite\One\User
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
            'id' => $user->uid, 'nickname' => $user->nickname,
            'name' => $user->name, 'email' => $user->email, 'avatar' => $user->imageUrl,
        ]);
    }

    /**
     * Get a Social User instance from a known access token and secret.
     *
     * @param  string $token
     * @param  string $secret
     * @return \mosaxiv\Socialite\One\User
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
            'id' => $user->uid, 'nickname' => $user->nickname,
            'name' => $user->name, 'email' => $user->email, 'avatar' => $user->imageUrl,
        ]);
    }

    /**
     * Get the token credentials for the request.
     *
     * @return \League\OAuth1\Client\Credentials\TokenCredentials
     */
    protected function getToken()
    {
        $temp = $this->request->getSession()->get('oauth.temp');
        return $this->server->getTokenCredentials(
            $temp,
            $this->request->get('oauth_token'),
            $this->request->get('oauth_verifier')
        );
    }

    /**
     * Determine if the request has the necessary OAuth verifier.
     *
     * @return bool
     */
    protected function hasNecessaryVerifier()
    {
        $hasToken = $this->request->get('oauth_token') !== null;
        $hasVerifier = $this->request->get('oauth_verifier') !== null;

        return $hasToken && $hasVerifier;
    }

    /**
     * Set the request instance.
     *
     * @param  \Symfony\Component\HttpFoundation\Request $request
     * @return $this
     */
    public function setRequest(Request $request)
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
