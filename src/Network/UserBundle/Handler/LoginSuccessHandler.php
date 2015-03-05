<?php

namespace Network\UserBundle\Handler;

use Doctrine\ORM\EntityManager;
use Symfony\Component\Security\Http\Authentication\DefaultAuthenticationSuccessHandler;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\SecurityContext;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Routing\Router;
use Symfony\Component\Security\Http\HttpUtils;

class LoginSuccessHandler extends DefaultAuthenticationSuccessHandler
{

    protected $router;
    protected $security;
    protected $em;

    public function __construct(HttpUtils $httpUtils, SecurityContext $security, EntityManager $em)
    {
        parent::__construct($httpUtils, []);
        $this->security = $security;
        $this->em = $em;
    }

    public function onAuthenticationSuccess(Request $request, TokenInterface $token)
    {
        $user = $this->security->getToken()->getUser();
        $user->setWebSocketAuthKey(uniqid());
        $this->em->persist($user);
        $this->em->flush();

        return parent::onAuthenticationSuccess($request, $token);

    }
}
