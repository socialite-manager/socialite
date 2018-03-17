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
        return 'https://accounts.google.com/o/oauth2/token';
    }

    /**
     * {@inheritdoc}
     */
    protected function getTokenFields(string $code)
    {
        $fields = parent::getTokenFields($code);
        $fields['grant_type'] = 'authorization_code';

        return $fields;
    }

    /**
     * {@inheritdoc}
     */
    protected function getUserByToken(string $token)
    {
        $response = $this->getHttpClient()->get('https://www.googleapis.com/plus/v1/people/me?', [
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
        return (new User)->setRaw($user)->map([
            'id' => $user['id'],
            'nickname' => A::get($user, 'nickname'),
            'name' => A::get($user, 'displayName'),
            'email' => A::get($user, 'emails.0.value'),
            'avatar' => A::get($user, 'image.url'),
            'avatar_original' => preg_replace('/\?sz=([0-9]+)/', '', A::get($user, 'image.url')),
        ]);
    }
}
