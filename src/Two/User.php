<?php
namespace Socialite\Two;

use Socialite\AbstractUser;

class User extends AbstractUser
{
    /**
     * The user's access token.
     *
     * @var string
     */
    public $token;

    /**
     * The refresh token that can be exchanged for a new access token.
     *
     * @var null|string
     */
    public $refreshToken;

    /**
     * The number of seconds the access token is valid for.
     *
     * @var null|int
     */
    public $expiresIn;

    /**
     * Set the token on the user.
     *
     * @param string $token
     * @return $this
     */
    public function setToken(string $token)
    {
        $this->token = $token;
        return $this;
    }

    /**
     * Set the refresh token required to obtain a new access token.
     *
     * @param string|null $refreshToken
     * @return $this
     */
    public function setRefreshToken(string $refreshToken = null)
    {
        $this->refreshToken = $refreshToken;
        return $this;
    }

    /**
     * Set the number of seconds the access token is valid for.
     *
     * @param int|null $expiresIn
     * @return $this
     */
    public function setExpiresIn(int $expiresIn = null)
    {
        $this->expiresIn = $expiresIn;
        return $this;
    }
}
