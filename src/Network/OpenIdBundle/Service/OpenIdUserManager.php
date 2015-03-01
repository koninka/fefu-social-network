<?php
/**
 * Created by PhpStorm.
 * User: user
 * Date: 01.03.2015
 * Time: 12:19
 */

namespace Network\OpenIdBundle\Service;


use Fp\OpenIdBundle\Model\UserManager;
use Fp\OpenIdBundle\Model\IdentityManagerInterface;
use Doctrine\ORM\EntityManager;
use Network\StoreBundle\Entity\OpenIdIdentity;
use Symfony\Component\Security\Core\Exception\BadCredentialsException;
use Symfony\Component\Security\Core\Exception\InsufficientAuthenticationException;

class OpenIdUserManager extends UserManager
{
    public function __construct(IdentityManagerInterface $identityManager, EntityManager $entityManager)
    {
        parent::__construct($identityManager);

        $this->entityManager = $entityManager;
    }

    public function createUserFromIdentity($identity, array $attributes = array())
    {
        if (false === isset($attributes['contact/email'])) {
            throw new \Exception('We need your e-mail address!');
        }

        $user = $this->entityManager->getRepository('NetworkStoreBundle:User')->findOneBy(array(
            'email' => $attributes['contact/email']
        ));

        if (null === $user) {
            throw new BadCredentialsException('No corresponding user!');
        }

        $openIdIdentity = new OpenIdIdentity();
        $openIdIdentity->setIdentity($identity)
                       ->setAttributes($attributes)
                       ->setUser($user);

        $this->entityManager->persist($openIdIdentity);
        $this->entityManager->flush();

        return $user;
    }
}
