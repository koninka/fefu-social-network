<?php

namespace Network\UserBundle\Mailer;

use FOS\UserBundle\Model\UserInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use FOS\UserBundle\Mailer\TwigSwiftMailer as BaseSwiftMailer;

class TwigSwiftMailer extends BaseSwiftMailer
{

    public function sendEmailMessage(UserInterface $user)
    {
        $template = $this->parameters['template']['new'];
        $url = $this->router->generate(
            'fos_user_registration_confirm_email',
            ['token' => $user->getConfirmationToken(), 'email' => $user->getEmail()],
            true
        );

        $context = ['user' => $user, 'confirmationUrl' => $url];
        $this->sendMessage($template, $context, $this->parameters['from_email']['confirmation'], $user->getEmail());
    }

}
