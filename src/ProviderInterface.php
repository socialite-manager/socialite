<?php
namespace Socialite;

interface ProviderInterface
{
    /**
     * Redirect the user to the authentication page for the provider.
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function redirect();

    /**
     * Redirect the user to the authentication page for the provider.
     *
     * @return \Zend\Diactoros\Response\RedirectResponse
     */
    public function psr7Redirect();

    /**
     * Get the User instance for the authenticated user.
     *
     * @return \Socialite\UserInterface
     */
    public function user();
}
