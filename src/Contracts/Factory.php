<?php
namespace mosaxiv\Socialite\Contracts;

interface Factory
{
    /**
     * Get an OAuth provider implementation.
     *
     * @param  string $driver
     * @return \mosaxiv\Socialite\Contracts\Provider
     */
    public function driver($driver = null);
}
