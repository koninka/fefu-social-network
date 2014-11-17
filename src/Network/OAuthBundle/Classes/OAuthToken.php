<?php

namespace Network\OAuthBundle\Classes;

use HWI\Bundle\OAuthBundle\OAuth\Response;
use HWI\Bundle\OAuthBundle\OAuth\Response\PathUserResponse;
use HWI\Bundle\OAuthBundle\OAuth\Response\UserResponseInterface;


class OAuthToken extends PathUserResponse
{
    public function getOAuthToken(UserResponseInterface $response)
    {
        return $response->oAuthToken;
    }
}
