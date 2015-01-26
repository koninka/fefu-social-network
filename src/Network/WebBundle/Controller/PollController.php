<?php

namespace Network\WebBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Network\StoreBundle\Entity\Poll;
use Network\UserBundle\Form\Type\PollType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;

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

            return new JsonResponse([
                'status' => 'ok',
                'pollId' => $poll->getId()
             ]);
        }

        return $this->render('NetworkWebBundle:Poll:createPoll.html.twig', ['form' =>  $form->createView()]);
    }

    public function editAction($id, Request $request)
    {
        $user = $this->getUser();
        $doctrine = $this->getDoctrine();
        $poll = $doctrine->getRepository('NetworkStoreBundle:Poll')->find($id);
        if (empty($user) || empty($poll)) {
            return $this->redirect($this->generateUrl('mainpage'));
        }
        if ($user != $poll->getOwner()
            || $doctrine->getRepository('NetworkStoreBundle:PollAnswer')->hasVoted($poll)) {
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

            return $this->redirect($this->generateUrl('user_profile', ['id' => $user->getId()]));
        }

        return $this->render('NetworkWebBundle:Poll:poll.html.twig', [
            'form' =>  $form->createView()
        ]);
    }

    public function deleteAction($id)
    {
        $user = $this->getUser();
        $doctrine = $this->getDoctrine();
        $poll = $doctrine->getRepository('NetworkStoreBundle:Poll')->find($id);
        if (empty($user) || empty($poll)) {
            return $this->redirect($this->generateUrl('mainpage'));
        }
        if ($user != $poll->getOwner()) {
            return $this->redirect($this->generateUrl('user_profile', ['id' => $user->getId()]));
        }
        $em = $this->container->get('doctrine')->getManager();

        $em->remove($poll);
        $em->flush();

        return $this->render('NetworkWebBundle:User:msg.html.twig', [
            'msg' => 'msg.delete_poll'
        ]);
    }

    public function pollAction(Request $request, $id)
    {
        $user = $this->getUser();
        if (null === $user) {
            return new JsonResponse([
                'status' => 'badUser',
            ]);
        }
        $data = json_decode($request->getContent(), true);
        if (null === $data ) {
            return new JsonResponse([
                'status' => 'badMsg',
            ]);
        }
        $doctrine = $this->getDoctrine();
        $pollAnswerRep = $doctrine->getRepository('NetworkStoreBundle:PollAnswer');
        if (array_key_exists('pollId', $data) ) {
            $poll = $doctrine->getRepository('NetworkStoreBundle:Poll')->find($data['pollId']);
        } else {
            $poll = $pollAnswerRep->getPoll($data['postId'])[0];
        }
        if (array_key_exists('postId', $data) ) {
            $post = $doctrine->getRepository('NetworkStoreBundle:Post')->find($data['postId']);
            $threadId = $post->getThread()->getId();
        } else {
            $threadId = $data['threadId'];
        }
        if (null === $poll) {
            return new JsonResponse([
                'status' => 'badPoll',
            ]);
        }
        $isUser = $pollAnswerRep->isUserAnswer($poll, $user->getId());
        $em = $this->container->get('doctrine')->getManager();
        if (array_key_exists('answer', $data) && !$isUser ) {
            $data = json_decode($request->getContent(), true);
            $key = $data['answer'];
            $ans =  $pollAnswerRep->find($key);
            if (!empty($ans) && $ans->getPoll() == $poll) {
                $isUser = true;
                $ans->AddUser($user);
                $em->persist($ans);
                $em->flush();
            }
        }
        $answer = [];
        $percent = [];
        $sum = $pollAnswerRep->countAnswer($poll);
        foreach ($poll->getAnswers() as $ans) {
            $answer[] = [$ans->getId(), $ans->getAnswer()];
            $percent[$ans->getId()] = $ans->getPercent($sum);
        }

        return new JsonResponse([
            'status' => 'ok',
            'id' => $poll->getId(),
            'answer' => $answer,
            'question' => $poll->getQuestion(),
            'thread_id' => $threadId,
            'percent' => $percent,
            'sum' => $sum,
            'isAnswer' => $isUser,
            'isAnonymously' => $poll->getIsAnonymously(),
            'isOwner' => $user === $poll->getOwner(),
        ]);

    }
    
    public function getUserAnswerJsonAction(Request $request)
    {
        $data = json_decode($request->getContent(), true);
        $pollAnswerRep = $this->getDoctrine()->getRepository('NetworkStoreBundle:PollAnswer');
        if (null === $data || !array_key_exists('answer', $data)) {
            return new JsonResponse([
                'status' => 'badMsg',
            ]);
        }
        $ans =  $pollAnswerRep->find($data['answer']);
        $rels = $this->get('network.store.poll_service')->getPollInfo($ans);

        return new JsonResponse($rels);
    }
}
