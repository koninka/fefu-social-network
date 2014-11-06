<?php

namespace Network\WebBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class KladrController extends Controller
{
    public function testAction()
    {
        return $this->render('NetworkWebBundle:Kladr:test.html.twig');
    }

}

