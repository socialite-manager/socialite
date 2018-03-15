<?php

namespace mosaxiv\Socialite;

trait SessionTrait
{
    /**
     * The Session instance.
     *
     * @var mixed
     */
    protected $session;

    /**
     * Set the session instance.
     *
     * @param mixed $session
     * @return $this
     */
    public function setSession($session)
    {
        $this->session = $session;
        return $this;
    }

    /**
     * Get the session instance.
     *
     * @return mixed
     */
    public function getSession()
    {
        return $this->session;
    }

    /**
     * Set Session data
     *
     * @param string $key
     * @param mixed $val
     * @return bool|mixed
     */
    protected function setSessionData(string $key, $val)
    {
        if (method_exists($this->getSession(), 'put')) {
            return $this->getSession()->put($key, $val);
        } elseif (method_exists($this->getSession(), 'set')) {
            return $this->getSession()->set($key, $val);
        } elseif (method_exists($this->getSession(), 'write')) {
            return $this->getSession()->write($key, $val);
        }

        return false;
    }

    /**
     * Get Session data
     *
     * @param string $key
     * @return bool|mixed
     */
    protected function getSessionData(string $key)
    {
        if (method_exists($this->getSession(), 'get')) {
            return $this->getSession()->get($key);
        } elseif (method_exists($this->getSession(), 'read')) {
            return $this->getSession()->read($key);
        }

        return false;
    }
}
