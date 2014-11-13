<?php

namespace Network\OAuthBundle\Authorization;

use HWI\Bundle\OAuthBundle\Security\Core\User\OAuthAwareUserProviderInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use HWI\Bundle\OAuthBundle\OAuth\Response\UserResponseInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Doctrine\Common\Persistence\ManagerRegistry;

class OAuthUserProvider implements UserProviderInterface, OAuthAwareUserProviderInterface
{

    /**
     * @var mixed
     */
    protected $em;

    /**
     * @var string
     */
    protected $className;

    /**
     * @var mixed
     */
    protected $repository;

    // protected $logger;

    public function __construct(ManagerRegistry $registry, $className)
    {
        $this->em = $registry->getManager();
        $this->repository = $this->em->getRepository($className);
        $this->className = $className;
    }

    public function loadUserByOAuthUserResponse(UserResponseInterface $response)
    {
        $username = $response->getUsername();
        $realname = explode(' ', $response->getRealname());
        $email = "$username@vkontakte.com"; //TODO get real email
        $gender = 'male'; //TODO get real gender
        $resourceOwnerName = $response->getResourceOwner()->getName();

        $user = $this->repository->findOneBy(
            array('email' => $email)
        );

        if (null === $user) {
            $user = new $this->className();
            $user->setUsername($username)
                 ->setPassword($resourceOwnerName)
                 ->setSalt($resourceOwnerName)
                 ->setFirstName($realname[1])
                 ->setLastName($realname[0])
                 ->setGender('male')
                 ->setEmail($email);
            $this->em->persist($user);
            $this->em->flush();
        }

        return $user;
    }

    public function loadUserByUsername($username)
    {
        $user = $this->repository->findOneBy(array('username' => $username));
        if (!$user) {
            throw new UsernameNotFoundException(sprintf("User '%s' not found.", $username));
        }

        return $user;
    }

    public function refreshUser(UserInterface $user)
    {
        return $this->loadUserByUsername($user->getUsername());
    }

    public function supportsClass($class)
    {
        return $class === $this->class || is_subclass_of($class, $this->class);
    }
}
