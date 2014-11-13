<?php

namespace Network\OAuthBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class DefaultController extends Controller
{
    public function indexAction($name)
    {
        return $this->render('NetworkOAuthBundle:Default:index.html.twig', array('name' => $name));
    }
}
