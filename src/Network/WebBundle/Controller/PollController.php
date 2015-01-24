<?php

namespace Network\WebBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Network\StoreBundle\Entity\Poll;
use Network\UserBundle\Form\Type\PollType;
use Symfony\Component\HttpFoundation\Request;

class PollController extends Controller
{
    public function createAction(Request $request)
    {
        $user = $this->getUser();
        if (empty($user)) {
            return $this->redirect($this->generateUrl('mainpage'));
        }
        $poll = new Poll();
        $form = $this->container->get('form.factory')->create(
            new PollType(),
            $poll
        );
        $form->handleRequest($request);

        if ($form->isValid()) {
            $em = $this->container->get('doctrine')->getManager();
            $poll->setOwner($user);
            $em->persist($poll);
            $em->flush();

            return $this->redirect( $this->generateUrl('user_poll', ['id' => $poll->getId()]));
        }

        return $this->render('NetworkWebBundle:Poll:createPoll.html.twig', [
            'form' =>  $form->createView()
        ]);
    }

    public function editAction($id, Request $request)
    {
        $user = $this->getUser();
        $poll = $this->getDoctrine()->getRepository('NetworkStoreBundle:Poll')->find($id);
        if (empty($user) || empty($poll)) {
            return $this->redirect($this->generateUrl('mainpage'));
        }
        if ($user != $poll->getOwner()
            || $this->getDoctrine()->getRepository('NetworkStoreBundle:PollAnswer')->hasVoted($poll)) {
            return $this->redirect($this->generateUrl('user_poll', ['id' => $poll->getId()]));
        }
        $form = $this->container->get('form.factory')->create(
            new PollType(),
            $poll
        );
        $form->handleRequest($request);
        if ($form->isValid()) {
            $em = $this->container->get('doctrine')->getManager();
            $em->persist($poll);
            $em->flush();

            return $this->redirect($this->generateUrl('user_poll', ['id' => $poll->getId()]));
        }

        return $this->render('NetworkWebBundle:Poll:createPoll.html.twig', [
            'form' =>  $form->createView()
        ]);
    }

    public function deleteAction($id)
    {
        $user = $this->getUser();
        $poll = $this->getDoctrine()->getRepository('NetworkStoreBundle:Poll')->find($id);
        if (empty($user) || empty($poll)) {
            return $this->redirect($this->generateUrl('mainpage'));
        }
        if ($user != $poll->getOwner()) {
            return $this->redirect($this->generateUrl('user_poll', ['id' => $poll->getId()]));
        }
        $em = $this->container->get('doctrine')->getManager();

        $em->remove($poll);
        $em->flush();

        return $this->render('NetworkWebBundle:User:msg.html.twig', [
            'msg' => 'msg.delete_poll'
        ]);
    }

    public function pollAction($id, Request $request)
    {
        $user = $this->getUser();
        $poll = $this->getDoctrine()->getRepository('NetworkStoreBundle:Poll')->find($id);
        if (empty($poll)) {
            return $this->redirect($this->generateUrl('mainpage'));
        }
        $isUser = !empty($user)
                ? $this->getDoctrine()->getRepository('NetworkStoreBundle:PollAnswer')->isUserAnswer($poll, $user->getId())
                : true;
        $em = $this->container->get('doctrine')->getManager();
        if ($request->getMethod() == 'POST' && !$isUser) {
            $key = $request->get('answer');
            $ans =  $this->getDoctrine()->getRepository('NetworkStoreBundle:PollAnswer')->find($key);
            if (!empty($ans) && $ans->getPoll() == $poll) {
                $isUser = true;
                $ans->AddUser($user);
                $em->persist($ans);
                $em->flush();
            }
        }
        $sum = 0;
        foreach ($poll->getAnswers() as $ans) {
            $sum += $ans->getUser()->count();
        }

        return $this->render('NetworkWebBundle:Poll:poll.html.twig', [
            'poll' => $poll,
            'sum' => $sum,
            'isAnswer' => $isUser,
            'isAnonymously' => $poll->getIsAnonymously(),
            'isOwner' => $user === $poll->getOwner(),
        ]);

    }
}
