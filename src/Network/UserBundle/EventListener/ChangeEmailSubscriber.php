<?php

namespace Network\UserBundle\EventListener;

use FOS\UserBundle\FOSUserEvents;
use FOS\UserBundle\Event\GetResponseUserEvent;
use FOS\UserBundle\Event\FormEvent;
use Network\UserBundle\Mailer\TwigSwiftMailer;
use FOS\UserBundle\Util\TokenGeneratorInterface;
use Symfony\Component\Security\Core\SecurityContextInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class ChangeEmailSubscriber implements EventSubscriberInterface
{
    private $mailer;
    private $tokenGenerator;
    private $router;
    private $session;
    private $security;

    public function __construct(
        TwigSwiftMailer $mailer,
        TokenGeneratorInterface $tokenGenerator,
        UrlGeneratorInterface $router,
        SessionInterface $session,
        SecurityContextInterface $security
    ) {
        $this->mailer = $mailer;
        $this->tokenGenerator = $tokenGenerator;
        $this->router = $router;
        $this->session = $session;
        $this->security = $security;
    }

    public static function getSubscribedEvents()
    {
        return [
                FOSUserEvents::PROFILE_EDIT_INITIALIZE => 'onProfileEditInitialize',
                FOSUserEvents::PROFILE_EDIT_SUCCESS => 'onProfileEditSuccess',
        ];
    }

    public function onProfileEditInitialize(GetResponseUserEvent $event)
    {
        // required, because when Success's event is called, session already contains new email
        $this->email = $this->security->getToken()->getUser()->getEmail();
    }

    public function onProfileEditSuccess(FormEvent $event)
    {
        $user = $event->getForm()->getData();

        if ($user->getEmail() !== $this->email) {
            $user->setConfirmationToken($this->tokenGenerator->generateToken());
            $this->mailer->sendEmailMessage($user);
            $this->security->setToken();
            $user->setEmail($this->email);
            $this->session->set('fos_user_send_confirmation_email/email', $user->getEmail());
            $url = $this->router->generate('fos_user_registration_check_email');
            $event->setResponse(new RedirectResponse($url));
        }
    }

}
