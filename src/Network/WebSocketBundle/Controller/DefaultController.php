<?php

namespace Network\WebSocketBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class DefaultController extends Controller
{
    public function indexAction()
    {
        return $this->render('NetworkWebSocketBundle:Default:index.html.twig');
    }
}
