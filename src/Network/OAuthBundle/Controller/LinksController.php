<?php

namespace Network\OAuthBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;


class LinksController extends Controller
{

    public function showLinksAction()
    {
        $user = $this->container->get('security.context')->getToken()->getUser();
        $em = $this->getDoctrine()->getManager();
        $query = $em->createQueryBuilder()->select('u')->from('NetworkStoreBundle:OpenIdIdentity', 'u')->
                andWhere('u.user = :id')->setParameter('id', $user->getId())->getQuery();
        $identity = $query->getResult();

        return $this->render('NetworkOAuthBundle::links.edit.html.twig',
            ['user' => $user, 'identity' => $identity]
        );
    }


    public function removeLinkAction($service)
    {
        $user = $this->container->get('security.context')->getToken()->getUser();
        if ($service == 'steam') {
            $em = $this->getDoctrine()->getManager();
            $query = $em->createQueryBuilder()->delete()->from('NetworkStoreBundle:OpenIdIdentity', 'u')->
                    andWhere('u.user = :id')->setParameter('id', $user->getId())->getQuery();
            $query->execute();

            return new RedirectResponse($this->generateUrl('show_links'));
        }
        $this->get('network_oauth.oauth_user_provider')->updateUserResourceLogin($user, $service, null);
        $userManager = $this->get('fos_user.user_manager');
        $userManager->updateUser($user);

        return new RedirectResponse($this->generateUrl('show_links'));
    }

}
