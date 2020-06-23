<?php
namespace Socialite\Two;

use Socialite\Util\A;

class GoogleProvider extends AbstractProvider
{
    /**
     * The separating character for the requested scopes.
     *
     * @var string
     */
    protected $scopeSeparator = ' ';

    /**
     * The scopes being requested.
     *
     * @var array
     */
    protected $scopes = [
        'openid',
        'profile',
        'email',
    ];

    /**
     * {@inheritdoc}
     */
    protected function getAuthUrl(string $state)
    {
        return $this->buildAuthUrlFromBase('https://accounts.google.com/o/oauth2/auth', $state);
    }

    /**
     * {@inheritdoc}
     */
    protected function getTokenUrl()
    {
        return 'https://www.googleapis.com/oauth2/v4/token';
    }

    /**
     * Get the POST fields for the token request.
     *
     * @param  string  $code
     * @return array
     */
    protected function getTokenFields(string $code)
    {
        return A::add(
            parent::getTokenFields($code), 'grant_type', 'authorization_code'
        );
    }

    /**
     * {@inheritdoc}
     */
    protected function getUserByToken(string $token)
    {
        $response = $this->getHttpClient()->get('https://www.googleapis.com/oauth2/v3/userinfo', [
            'query' => [
                'prettyPrint' => 'false',
            ],
            'headers' => [
                'Accept' => 'application/json',
                'Authorization' => 'Bearer ' . $token,
            ],
        ]);
        return json_decode($response->getBody(), true);
    }

    /**
     * {@inheritdoc}
     */
    protected function mapUserToObject(array $user)
    {
        // Deprecated: Fields added to keep backwards compatibility in 4.0. These will be removed in 5.0
        $user['id'] = A::get($user, 'sub');
        $user['verified_email'] = A::get($user, 'email_verified');
        $user['link'] = A::get($user, 'profile');

        return (new User)->setRaw($user)->map([
            'id' => A::get($user, 'sub'),
            'nickname' => A::get($user, 'nickname'),
            'name' => A::get($user, 'name'),
            'email' => A::get($user, 'email'),
            'avatar' => $avatarUrl = A::get($user, 'picture'),
            'avatar_original' => $avatarUrl,
        ]);
    }
}
