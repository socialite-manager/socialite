<?php
namespace mosaxiv\Socialite\Two;

use mosaxiv\Socialite\Util\A;

class BitbucketProvider extends AbstractProvider
{
    /**
     * The scopes being requested.
     *
     * @var array
     */
    protected $scopes = ['email'];

    /**
     * The separating character for the requested scopes.
     *
     * @var string
     */
    protected $scopeSeparator = ' ';

    /**
     * {@inheritdoc}
     */
    protected function getAuthUrl(string $state)
    {
        return $this->buildAuthUrlFromBase('https://bitbucket.org/site/oauth2/authorize', $state);
    }

    /**
     * {@inheritdoc}
     */
    protected function getTokenUrl()
    {
        return 'https://bitbucket.org/site/oauth2/access_token';
    }

    /**
     * {@inheritdoc}
     */
    protected function getUserByToken(string $token)
    {
        $userUrl = 'https://api.bitbucket.org/2.0/user?access_token=' . $token;
        $response = $this->getHttpClient()->get($userUrl);
        $user = json_decode($response->getBody(), true);
        if (in_array('email', $this->scopes)) {
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
    protected function getEmailByToken($token)
    {
        $emailsUrl = 'https://api.bitbucket.org/2.0/user/emails?access_token=' . $token;
        try {
            $response = $this->getHttpClient()->get($emailsUrl);
        } catch (\Exception $e) {
            return;
        }
        $emails = json_decode($response->getBody(), true);
        foreach ($emails['values'] as $email) {
            if ($email['type'] == 'email' && $email['is_primary'] && $email['is_confirmed']) {
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
            'id' => $user['uuid'],
            'nickname' => $user['username'],
            'name' => A::get($user, 'display_name'),
            'email' => A::get($user, 'email'),
            'avatar' =>A::get($user, 'links.avatar.href'),
        ]);
    }

    /**
     * Get the access token for the given code.
     *
     * @param string $code
     * @return string
     */
    public function getAccessToken(string $code)
    {
        $response = $this->getHttpClient()->post($this->getTokenUrl(), [
            'auth' => [$this->clientId, $this->clientSecret],
            'headers' => ['Accept' => 'application/json'],
            'form_params' => $this->getTokenFields($code),
        ]);
        return json_decode($response->getBody(), true)['access_token'];
    }

    /**
     * Get the POST fields for the token request.
     *
     * @param string $code
     * @return array
     */
    protected function getTokenFields(string $code)
    {
        return parent::getTokenFields($code) + ['grant_type' => 'authorization_code'];
    }
}
