<?php

namespace Network\OpenIdBundle\Controller;

use FOS\UserBundle\Event\FilterUserResponseEvent;
use FOS\UserBundle\Event\GetResponseUserEvent;
use FOS\UserBundle\FOSUserEvents;
use HWI\Bundle\OAuthBundle\Security\Core\Authentication\Token\OAuthToken;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\SecurityContext;
use Network\StoreBundle\Entity\User;
use Fp\OpenIdBundle\RelyingParty\Exception\OpenIdAuthenticationCanceledException;
use Fp\OpenIdBundle\RelyingParty\RecoveredFailureRelyingParty;
use Fp\OpenIdBundle\Security\Core\Authentication\Token\OpenIdToken;

class SecurityController extends Controller
{
    public function finishOpenIdLoginAction(Request $request)
    {
        $failure = $request->getSession()->get(SecurityContext::AUTHENTICATION_ERROR);
        if (false == $failure) {
            throw new \LogicException('The controller expect AuthenticationException to be present in session');
        }
        if ($failure instanceof OpenIdAuthenticationCanceledException) {
            return $this->redirect('/');
        }
        /**
         * @var $token OpenIdToken
         */
        $token = $failure->getToken();
        if (false == $token instanceof OpenIdToken) {
            throw new \LogicException('The failure does not contain OpenIdToken, Is the failure come from openid?');
        }
        $attributes = array_merge(array(
            'contact/email' => '',
            'namePerson/first' => '',
            'namePerson/last' => '',
        ), $token->getAttributes());
        $identity = self::getIdentityManager()->findByIdentity($token->getIdentity());
        if (null !== $identity) {
            $user = $identity->getUser();
            if (null !== $user) {
                $token = new OAuthToken($identity, $user->getRoles());
                $token->setResourceOwnerName('Steam');
                $token->setUser($user);
                $token->setAuthenticated(true);
                $this->container->get('security.context')->setToken($token);
                $url = $this->generateUrl('fos_user_profile_show');
                $response = new RedirectResponse($url);

                return $response;
            }
        }
        $curToken = $this->container->get('security.context')->getToken();
        if (null !== $curToken && $curToken->getUser() && $curToken->getUser() !== 'anon.') {
            $user = $curToken->getUser();
            $identity = $this->getIdentityManager()->create();
            $identity->setAttributes($attributes)
                     ->setUser($user);
            $this->getIdentityManager()->update($identity);
            $url = $this->generateUrl('fos_user_profile_show');
            $response = new RedirectResponse($url);

            return $response;
        }
        $this->getDoctrine()->getManager();
        $user = $this->getUserManager()->createUser();
        $user->setEmail($attributes['contact/email'])
            ->setFirstName($attributes['namePerson/first'])
            ->setLastName($attributes['namePerson/last']);

        /** @var $formFactory \FOS\UserBundle\Form\Factory\FactoryInterface */
        $formFactory = $this->get('fos_user.registration.form.factory');
        /** @var $userManager \FOS\UserBundle\Model\UserManagerInterface */
        $userManager = $this->get('fos_user.user_manager');
        /** @var $dispatcher \Symfony\Component\EventDispatcher\EventDispatcherInterface */
        $dispatcher = $this->get('event_dispatcher');

        $event = new GetResponseUserEvent($user, $request);
        $dispatcher->dispatch(FOSUserEvents::REGISTRATION_INITIALIZE, $event);

        if (null !== $event->getResponse()) {
            return $event->getResponse();
        }

        $form = $formFactory->createForm();
        $form->setData($user);
        $form->handleRequest($request);

        if ($form->isValid()) {
            $user->setEnabled(true)
                 ->setUsername($user->getEmail());
            $userManager->updateUser($user);
            $identity = $this->getIdentityManager()->create();
            $identity->setIdentity($token->getIdentity())
                     ->setAttributes($attributes)
                     ->setUser($user);
            $this->getIdentityManager()->update($identity);
            $url = $this->generateUrl('fos_user_profile_show');
            $response = new RedirectResponse($url);
            $dispatcher->dispatch(FOSUserEvents::REGISTRATION_COMPLETED, new FilterUserResponseEvent($user, $request, $response));

            return $response;
        }

        return $this->render('NetworkOpenIdBundle::resume_openid_registration.html.twig', array(
            'form' => $form->createView(),
        ));
    }

    protected function getIdentityManager()
    {
        return $this->get('fp_openid.identity_manager');
    }

    protected function getUserManager()
    {
        return $this->get('fos_user.user_manager');
    }
}
