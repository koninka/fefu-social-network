<?php
/**
 * Created by PhpStorm.
 * User: user
 * Date: 18.01.2015
 * Time: 16:05
 */
namespace Network\OAuthBundle\Authorization;

use FOS\UserBundle\Event\UserEvent;
use FOS\UserBundle\FOSUserEvents;
use FOS\UserBundle\Model\UserManagerInterface;
use HWI\Bundle\OAuthBundle\OAuth\Response\UserResponseInterface;
use HWI\Bundle\OAuthBundle\Security\Core\Authentication\Token\OAuthToken;
use HWI\Bundle\OAuthBundle\Security\Core\User\FOSUBUserProvider as BaseClass;
use Network\StoreBundle\Entity\ContactInfo;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\PropertyAccess\PropertyAccess;
use Symfony\Component\Security\Core\User\UserInterface;

class AuthProvider extends BaseClass
{
    private $session;

    public function __construct(UserManagerInterface $userManager, Session $session, array $properties)
    {
        $this->userManager = $userManager;
        $this->session = $session;
        $this->properties  = array_merge($this->properties, $properties);
        $this->accessor    = PropertyAccess::createPropertyAccessor();
    }
    /**
     * {@inheritDoc}
     */
    public function connect(UserInterface $user, UserResponseInterface $response)
    {
        $property = $this->getProperty($response);
        $username = $response->getUsername();

        //on connect - get the access token and the user ID
        $service = $response->getResourceOwner()->getName();

        $setter = 'set'.ucfirst($service);
        $setter_id = $setter.'Id';
        $setter_token = $setter.'AccessToken';

        //we "disconnect" previously connected users
        if (null !== $previousUser = $this->userManager->findUserBy(array($property => $username))) {
            $previousUser->$setter_id(null);
            $previousUser->$setter_token(null);
            $this->userManager->updateUser($previousUser);
        }

        //we connect current user
        $user->$setter_id($username);
        $user->$setter_token($response->getAccessToken());

        $this->userManager->updateUser($user);
    }

    /**
     * {@inheritdoc}
     */
    public function loadUserByOAuthUserResponse(UserResponseInterface $response)
    {
        $username = $response->getUserName();
        $email = $response->getEmail();
        $id = $response->getId();
        $user = $this->userManager->findUserBy(array($this->getProperty($response) => $id));
        if (null === $user) {
            $user = $this->userManager->findUserBy(array('email' => $email));
        }
        //when the user is registrating
        if (null === $user) {
            $service = $response->getResourceOwner()->getName();
            $setter = 'set'.ucfirst($service);
            $setter_id = $setter.'Id';
            $setter_token = $setter.'AccessToken';
            // create new user here
            $user = $this->userManager->createUser();
            $user->$setter_id($response->getId());
            $user->$setter_token($response->getAccessToken());
            //I have set all requested data with the user's username
            //modify here with relevant data
            $user->setUsername($username);
            $user->setEmail($username.'@'.'.com');
            $user->setPassword(md5(rand()));
            $user->setEnabled(true);
            $user->setSalt(md5(rand()));
            $user->setGender('male');
            $user->setFirstName('adfnbl');
            $user->setLastName(' ');
            $user->setContactInfo(new ContactInfo());
            $this->userManager->updateUser($user);
            $token = new OAuthToken($response->getAccessToken(), $user->getRoles());
            $token->setResourceOwnerName($service);
            $token->setUser($user);
            $token->setAuthenticated(true);
           // update session
            $this->session->set('_security_secured_area', serialize($token));
            $this->session->save();
            return $user;
        }

        //if user exists - go with the HWIOAuth way
        $user = parent::loadUserByOAuthUserResponse($response);

        $serviceName = $response->getResourceOwner()->getName();
        $setter = 'set' . ucfirst($serviceName) . 'AccessToken';

        //update access token
        $user->$setter($response->getAccessToken());

        return $user;
    }

}