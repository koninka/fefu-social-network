<?php

namespace Network\OAuthBundle\Authorization;

use Doctrine\Common\Persistence\ManagerRegistry;
use HWI\Bundle\OAuthBundle\OAuth\Response\UserResponseInterface;
use HWI\Bundle\OAuthBundle\Security\Core\User\OAuthAwareUserProviderInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Network\OAuthBundle\Classes\OAuthToken;

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

    protected $oAuthToken;

    public function __construct(ManagerRegistry $registry, $className)
    {
        $this->em = $registry->getManager();
        $this->repository = $this->em->getRepository($className);
        $this->className = $className;
        $this->oAuthToken = new OAuthToken();
    }


    private function loginUserVK(UserResponseInterface $response)
    {
        $username = $response->getUsername();
        $resource = $response->getResourceOwner()->getName();
        $realname = explode(' ', $response->getRealname());
        $firstName = $realname[1];
        $lastName = $realname[0];
        $email = $this->oAuthToken->getOAuthToken($response)->getRawToken()['email'];
        $email = empty($email)
                ? "$username@$resource.com"
                : $email;

        return [
            'username' => $username,
            'firstName' => $firstName,
            'lastName' => $lastName,
            'gender' => 'male',
            'email' => $email,
        ];
    }


    private function loginUserGitHub(UserResponseInterface $response)
    {
        $username = $response->getNickname();
        $resource = $response->getResourceOwner()->getName();
        $firstName = 'Default first name';
        $lastName = 'Default last name';
        $rawToken = $this->oAuthToken->getOAuthToken($response)->getRawToken();
        $email = isset($rawToken['email']) && !empty($rawToken['email'])
            ? $rawToken['email']
            : "$username@$resource.com";

        return [
            'username' => $username,
            'firstName' => $firstName,
            'lastName' => $lastName,
            'gender' => 'male',
            'email' => $email,
        ];
    }


    private function loginUserFaceBook(UserResponseInterface $response)
    {
        $username = $response->getNickname();
        $resource = $response->getResourceOwner()->getName();
        $firstName = $response->getResponse()['first_name'];
        $lastName = $response->getResponse()['last_name'];
        $gender = $response->getResponse()['gender'];
        $email = empty($response->getEmail())
            ? "$username@$resource.com"
            : $response->getEmail();

        return [
            'username' => $username,
            'firstName' => $firstName,
            'lastName' => $lastName,
            'gender' => $gender,
            'email' => $email,
        ];
    }
    public function loadUserByOAuthUserResponse(UserResponseInterface $response)
    {
        switch ($response->getResourceOwner()->getName()) {
            case 'vkontakte' :
                $data = $this->loginUserVK($response);
                break;
            case 'github' :
                $data = $this->loginUserGitHub($response);
                break;
            case 'facebook' :
                $data = $this->loginUserFacebook($response);
                break;
            default :
                return null;
        }

        $user = $this->repository->findOneBy(
            ['email' => $data['email']]
        );

        if (null === $user) {
            $user = new $this->className();
            $user->setUsername($data['username'])
                 ->setPassword(' ')
                 ->setSalt(' ')
                 ->setFirstName($data['firstName'])
                 ->setLastName($data['lastName'])
                 ->setGender($data['gender'])
                 ->setEmail($data['email'])
                 ->setEnabled(true);
            $this->em->persist($user);
            $this->em->flush();
        }

        return $user;
    }

    public function loadUserByUsername($username)
    {
        $user = $this->repository->findOneBy(['username' => $username]);
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
