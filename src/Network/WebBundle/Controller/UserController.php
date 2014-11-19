<?php

namespace Network\WebBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Network\StoreBundle\Form\Type\UserType;
use Symfony\Component\HttpFoundation\Request;

class UserController extends Controller
{

    public function profileAction($id, Request $request)
    {
        $user = $this->getDoctrine()->getRepository('NetworkStoreBundle:User')->find($id);
        if (empty($user)) return $this->redirect($this->generateUrl('mainpage'));

        $oldPassword = $user ? $user->getPassword() : null;

        $form = $this->createForm(new UserType(), $user);
        $form->handleRequest($request);
        if ($form->isValid()) {
            $manager = $this->getDoctrine()->getManager();
            if (null == $user->getPassword()) {
                $user->setPassword($oldPassword);
            } else {
                $encoder = $this->get('security.encoder_factory')->getEncoder($user);
                $user->hash($encoder);
            }
            $manager->persist($user);
            $this->get('session')->getFlashBag()->add(
                'notice',
                'Data successfully edited'
            );
            $manager->flush();
        } else {
            $this->get('session')->getFlashBag()->add(
                'error',
                'Form data is invalid'
            );
        }

        return $this->render('NetworkWebBundle:User:profile.html.twig', [
            'form' => $form->createView(),
        ]);
    }

}
