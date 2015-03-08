<?php

namespace Network\OAuthBundle\Authorization;

use Doctrine\Common\Persistence\ManagerRegistry;
use HWI\Bundle\OAuthBundle\OAuth\Response\UserResponseInterface;
use HWI\Bundle\OAuthBundle\Security\Core\User\OAuthAwareUserProviderInterface;
use Symfony\Component\PropertyAccess\PropertyAccess;
use Symfony\Component\Security\Core\Exception\UsernameNotFoundException;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Network\OAuthBundle\Classes\OAuthToken;
use HWI\Bundle\OAuthBundle\Security\Core\Authentication\Token\OAuthToken as Token;
use Network\StoreBundle\Entity\ContactInfo;
use Symfony\Component\DependencyInjection\ContainerInterface;
use HWI\Bundle\OAuthBundle\Security\Core\User\FOSUBUserProvider as BaseClass;

class OAuthUserProvider extends BaseClass
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

    protected $container;

    protected $userManager;


    public function __construct(ManagerRegistry $registry, $className, ContainerInterface $container, $userNanager, array $properties)
    {
        $this->container = $container;
        $this->em = $registry->getManager();
        $this->repository = $this->em->getRepository($className);
        $this->className = $className;
        $this->oAuthToken = new OAuthToken();
        $this->userManager = $userNanager;
        $this->properties  = array_merge($this->properties, $properties);
        $this->accessor    = PropertyAccess::createPropertyAccessor();
    }


    private function loginUserVK(UserResponseInterface $response)
    {
        $username = $response->getUsername();
        $realname = explode(' ', $response->getRealname());
        $firstName = $realname[1];
        $lastName = $realname[0];
        $email = '';
        if ( isset($this->oAuthToken->getOAuthToken($response)->getRawToken()['email']) ) {
            $email = $this->oAuthToken->getOAuthToken($response)->getRawToken()['email'];
        }

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
        $id = $response->getResponse()['id'];

        return [
            'id' => $id,
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

    private function loginUserInstagram($response)
    {
        $data = $response->getResponse()['data'];
        $username = $data['username'];
        $id = $data['id'];
        $user = $this->userManager->findUserBy(array($this->getProperty($response) => $id));
        if (null === $user) {
            $service = $response->getResourceOwner();
            $setter = 'set' . ucfirst($service->getName());
            $setter_id = $setter . 'Id';
            $setter_token = $setter . 'AccessToken';
            $user = $this->userManager->createUser();
            $user->$setter_id($id);
            $user->$setter_token($response->getAccessToken());
            $user->setUsername($username)
                 ->setEmail($username . '@' . '.com')
                 ->setPassword(md5(rand()))
                 ->setEnabled(false)
                 ->setSalt(md5(rand()))
                 ->setGender('male')
                 ->setFirstName('adfnbl')
                 ->setLastName(' ')
                 ->setContactInfo(new ContactInfo());
            $this->userManager->updateUser($user);
            $token = new Token($response->getAccessToken(), $user->getRoles());
            $token->setResourceOwnerName($service->getName());
            $token->setUser($user);
            $token->setAuthenticated(false);

            return $user;
        }
        $user = parent::loadUserByOAuthUserResponse($response);
        $serviceName = $response->getResourceOwner()->getName();
        $setter = 'set' . ucfirst($serviceName) . 'AccessToken';
        //update access token
        $user->$setter($response->getAccessToken());
        $this->userManager->updateUser($user);

        return $user;
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
            case 'instagram':
                return $this->loginUserInstagram($response);
                break;
            default :
                return null;
        }

        $curToken = $this->container->get('security.context')->getToken();
        if (null != $curToken && $curToken->getUser() && $curToken->getUser()->getEnabled()) {
            $userByLogin = $this->repository->findOneBy(
                [$data['loginField'] => $data['username']]
            );

            if (!empty($userByLogin)) {
                $this->updateUserResourceLogin($userByLogin, $response->getResourceOwner()->getName(), null, $response);
            }

            $this->updateUserResourceLogin($curToken->getUser(), $response->getResourceOwner()->getName(), $data['username'], $response);

            return $curToken->getUser();
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
                 ->setWebSocketAuthKey(uniqid())
                 ->setContactInfo(new ContactInfo());
            $id = $data['username'];
            if (isset($data['id']) && $response->getResourceOwner()->getName() == 'facebook') {
                $user->setFacebookId($data['id']);
                $id = $data['id'];
            }
            $this->updateUserResourceLogin($user, $response->getResourceOwner()->getName(), $id, $response);
            $this->em->persist($user);
            $this->em->flush();
        }

        return $user;
    }


    public function updateUserResourceLogin(UserInterface $user, $field, $login, $response)
    {
        $setter = 'set' . ucfirst($field);
        $setter_id = $setter . 'Id';
        $setter_token = $setter . 'AccessToken';
        $user->$setter_id($login);
        $user->$setter_token($response->getAccessToken());
        switch ($field) {
            case 'vkontakte' :
                $user->setVkLogin($login);
                break;
            case 'github' :
                $user->setGithubLogin($login);
                break;
            case 'facebook' :
                $user->setFbLogin($login);
                break;
            case 'google' :
                $user->setGoogleLogin($login);
                break;
            default :
                break;
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
