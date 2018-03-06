<?php
namespace mosaxiv\Socialite\One;

use mosaxiv\Socialite\AbstractUser;

class User extends AbstractUser
{
    /**
     * The user's access token.
     *
     * @var string
     */
    public $token;

    /**
     * The user's access token secret.
     *
     * @var string
     */
    public $tokenSecret;

    /**
     * Set the token on the user.
     *
     * @param string $token
     * @param string $tokenSecret
     * @return $this
     */
    public function setToken(string $token,string $tokenSecret)
    {
        $this->token = $token;
        $this->tokenSecret = $tokenSecret;
        return $this;
    }
}
