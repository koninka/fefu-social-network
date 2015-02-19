<?php

namespace Network\OAuthBundle\Responses;

use HWI\Bundle\OAuthBundle\OAuth\Response\PathUserResponse;

/**
 * GoogleUserResponse
 *
 */
class InstagramResponse extends PathUserResponse
{
    /**
     * {@inheritdoc}
     */
    public function getId()
    {
        return $this->getValueForPath('identifier', false);
    }

    /**
     * {@inheritdoc}
     */
    public function getUserName()
    {
        return $this->getValueForPath('nickname', false);
    }
    /**
     * {@inheritdoc}
     */
    public function getProfilePicture()
    {
        return $this->getValueForPath('profilepicture', false);
    }

    /**
     * {@inheritdoc}
     */
    public function getFullName()
    {
        return $this->getValueForPath('full_name', false);
    }
}