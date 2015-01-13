<?php
namespace Network\WebBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Network\StoreBundle\Controller\FileController;

class Mp3PlayerController extends Controller
{
    public function mainAction()
    {
        $user = $this->getUser();
        if (null === $user) {
            return $this->redirect($this->generateUrl('mainpage'));
        }

        return $this->render('NetworkWebBundle:Mp3Player:main.html.twig', [
            'mp3s' => $user->getMp3s(),
            'filename' => FileController::UPLOADED_MP3_NAME,
        ]);
    }
}