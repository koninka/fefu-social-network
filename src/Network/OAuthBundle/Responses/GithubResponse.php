<?php
/**
 * Created by PhpStorm.
 * User: user
 * Date: 19.01.2015
 * Time: 13:13
 */
namespace Network\OAuthBundle\Responses;

use HWI\Bundle\OAuthBundle\OAuth\Response\PathUserResponse;

/**
 * GitHubserResponse
 *
 */
class GitHubResponse extends PathUserResponse
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