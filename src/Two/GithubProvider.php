<?php
namespace Socialite\Two;

use Socialite\Util\A;

class GithubProvider extends AbstractProvider
{
    /**
     * The scopes being requested.
     *
     * @var array
     */
    protected $scopes = ['user:email'];

    /**
     * {@inheritdoc}
     */
    protected function getAuthUrl(string $state)
    {
        return $this->buildAuthUrlFromBase('https://github.com/login/oauth/authorize', $state);
    }

    /**
     * {@inheritdoc}
     */
    protected function getTokenUrl()
    {
        return 'https://github.com/login/oauth/access_token';
    }

    /**
     * {@inheritdoc}
     */
    protected function getUserByToken(string $token)
    {
        $userUrl = 'https://api.github.com/user?access_token=' . $token;
        $response = $this->getHttpClient()->get(
            $userUrl,
            $this->getRequestOptions()
        );
        $user = json_decode($response->getBody(), true);
        if (in_array('user:email', $this->scopes)) {
            $user['email'] = $this->getEmailByToken($token);
        }
        return $user;
    }

    /**
     * Get the email for the given access token.
     *
     * @param string $token
     * @return string|null
     */
    protected function getEmailByToken(string $token)
    {
        $emailsUrl = 'https://api.github.com/user/emails?access_token=' . $token;
        try {
            $response = $this->getHttpClient()->get(
                $emailsUrl,
                $this->getRequestOptions()
            );
        } catch (\Exception $e) {
            return null;
        }
        foreach (json_decode($response->getBody(), true) as $email) {
            if ($email['primary'] && $email['verified']) {
                return $email['email'];
            }
        }
    }

    /**
     * {@inheritdoc}
     */
    protected function mapUserToObject(array $user)
    {
        return (new User)->setRaw($user)->map([
            'id' => $user['id'],
            'nickname' => A::get($user, 'login'),
            'name' => A::get($user, 'name'),
            'email' => A::get($user, 'email'),
            'avatar' => A::get($user, 'avatar_url'),
        ]);
    }

    /**
     * Get the default options for an HTTP request.
     *
     * @return array
     */
    protected function getRequestOptions()
    {
        return [
            'headers' => [
                'Accept' => 'application/vnd.github.v3+json',
            ],
        ];
    }
}
