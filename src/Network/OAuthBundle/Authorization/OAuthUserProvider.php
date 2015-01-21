<?php

namespace Network\OAuthBundle\Authorization;

use Doctrine\Common\Persistence\ManagerRegistry;
use HWI\Bundle\OAuthBundle\OAuth\Response\UserResponseInterface;
use HWI\Bundle\OAuthBundle\Security\Core\User\OAuthAwareUserProviderInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Network\OAuthBundle\Classes\OAuthToken;
use Network\StoreBundle\Entity\ContactInfo;

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
        $realname = explode(' ', $response->getRealname());
        $firstName = $realname[1];
        $lastName = $realname[0];
        $email = $this->oAuthToken->getOAuthToken($response)->getRawToken()['email'];

        return [
            'loginField' => 'vkLogin',
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
        $firstName = 'Default first name';
        $lastName = 'Default last name';
        $rawToken = $this->oAuthToken->getOAuthToken($response)->getRawToken();
        $email = isset($rawToken['email']) && !empty($rawToken['email']) ? $rawToken['email'] : '';

        return [
            'loginField' => 'githubLogin',
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
        $firstName = $response->getResponse()['first_name'];
        $lastName = $response->getResponse()['last_name'];
        $gender = $response->getResponse()['gender'];
        $email = $response->getEmail();

        return [
            'loginField' => 'fbLogin',
            'username' => $username,
            'firstName' => $firstName,
            'lastName' => $lastName,
            'gender' => $gender,
            'email' => $email,
        ];
    }


    private function loginUserGoogle(UserResponseInterface $response)
    {
        $username = $response->getUsername();
        $firstName = $response->getResponse()['given_name'];
        $lastName = $response->getResponse()['family_name'];
        $gender = $response->getResponse()['gender'];
        $email = $response->getEmail();

        return [
            'loginField' => 'googleLogin',
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
            case 'google' :
                $data = $this->loginUserGoogle($response);
                break;
            default :
                return null;
        }

        $user = $this->repository->findOneBy(
            [$data['loginField'] => $data['username']]
        );

        if (null === $user) {
            $email = empty($data['email']) || !empty($this->repository->findOneBy(['email' => $data['email']]))
                ? "@${data['username']}"
                : $data['email'];
            $user = new $this->className();
            $user->setUsername($data['username'])
                 ->setPassword(md5(rand()))
                 ->setSalt(' ')
                 ->setFirstName($data['firstName'])
                 ->setLastName($data['lastName'])
                 ->setGender($data['gender'])
                 ->setEmail($email)
                 ->setEnabled(false)
                 ->setContactInfo(new ContactInfo());
            switch ($data['loginField']) {
                case 'vkontakte' :
                    $user->setVkLogin($data['username']);
                    break;
                case 'github' :
                    $user->setGithubLogin($data['username']);
                    break;
                case 'facebook' :
                    $user->setFbLogin($data['username']);
                    break;
                case 'google' :
                    $user->setGoogleLogin($data['username']);
                    break;
                default :
                    break;
            }
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
