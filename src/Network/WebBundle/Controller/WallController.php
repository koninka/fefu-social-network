<?php
/**
 * Created by PhpStorm.
 * User: Артём
 * Date: 23.01.2015
 * Time: 15:27
 */

namespace Network\WebBundle\Controller;

use Network\StoreBundle\Entity\Post;
use Network\StoreBundle\Entity\Thread;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

class WallController extends Controller
{
    const LAZY_LOAD_PATCH_SIZE = 10;

    /**
     * @param string $objectType
     * @param int $id
     *
     * @return ArrayCollection | null
     */
    private function fetchWall($objectType, $id)
    {
        $wall = null;

        $object = $this->getDoctrine()
                       ->getRepository('NetworkStoreBundle:' . ('user' === $objectType ? 'User' : 'Community'))
                       ->find($id);

        if (null !== $object) {
            $wall = $object->getWallThreads();
        }

        return $wall;
    }

    public function mainAction($object)
    {
        $user = $this->getUser();
        if (null === $user) {
            return $this->redirect($this->generateUrl('mainpage'));
        }

        $wall = $object->getWallThreads();

        if (null === $wall) {
            throw new \Exception('Wrong object or id');
        }

        return $this->render('NetworkWebBundle:Wall:main.html.twig', [
            'wall' => $wall,
            'object' => $object,
            'patchSize' => static::LAZY_LOAD_PATCH_SIZE,
        ]);
    }

    public function writeAction(Request $request, $type, $id)
    {
        $user = $this->getUser();
        if (null === $user) {
            return new JsonResponse([
                'status' => 'badUser',
            ]);
        }

        $wall = $this->fetchWall($type, $id);

        if (null === $wall) {
            return new JsonResponse([
                'status' => 'badWall',
            ]);
        }

        $data = json_decode($request->getContent(), true);

        if (null === $data || !array_key_exists('msg', $data)) {
            return new JsonResponse([
                'status' => 'badMsg',
            ]);
        }

        $threadId = array_key_exists('threadId', $data) ? $data['threadId'] : -1;
        $em = $this->getDoctrine()->getManager();
        $post = new Post();
        $wallThread = null;
        $newThread = false;

        if ($threadId === -1) {
            $newThread = true;
            $wallThread = new Thread();

            $wall->add($wallThread);
            $em->persist($wallThread);
        } else {
            $wallThread = $this->getDoctrine()
                               ->getRepository('NetworkStoreBundle:Thread')
                               ->find($threadId);
        }

        $post->setUser($user)
             ->setTs(new \DateTime())
             ->setThread($wallThread)
             ->setText($data['msg']);

        $wallThread->addPost($post);

        $em->persist($post);
        $em->flush();

        return new JsonResponse([
            'status' => 'ok',
            'msg' => $post->getText(),
            'user_id' => $user->getId(),
            'ts' => $post->getTs(),
            'username' => $user->getFirstName() . ' ' . $user->getLastName(),
            'thread_id' => $wallThread->getId(),
            'post_id' => $post->getId(),
            'new_thread' => $newThread,
        ]);
    }

    public function deleteAction($type, $id, $post_id)
    {
        $user = $this->getUser();
        if (null === $user) {
            return new JsonResponse([
                'status' => 'badUser',
            ]);
        }

        $em = $this->getDoctrine()->getManager();
        $post = $this->getDoctrine()
                     ->getRepository('NetworkStoreBundle:Post')
                     ->find($post_id);
        $wallOwner = $this->getDoctrine()
                          ->getRepository('NetworkStoreBundle:Thread')
                          ->getUserByWallThreadId($post->getThread()->getId());

        $responseBody = [
            'status' => 'ok',
            'id' => $post_id,
        ];

        if (
            $post->getUser() === $user
            || $wallOwner === $user
        ) {
            $wallThread = $post->getThread();
            $threadDied = $wallThread->getPosts()->count() == 1
                          || $wallThread->getPosts()[0] == $post;

            $wallThread->removePost($post);
            $em->remove($post);

            if ($threadDied) {
                foreach ($wallThread->getPosts() as $wallPost) {
                    $em->remove($wallPost);
                }

                $wallOwner->removeWallThread($wallThread);
                $em->remove($wallThread);
            }

            $em->flush();
        } else {
            $responseBody['status'] = 'badPost';
        }

        return new JsonResponse($responseBody);
    }

    public function loadPostsAction($type, $id, $start)
    {
        $user = $this->getUser();
        if (null === $user) {
            return new JsonResponse([
                'status' => 'badUser',
            ]);
        }

        $responseBody = [
            'status' => 'ok',
            'threads' => [],
        ];

        $wall = $this->fetchWall($type, $id);

        $reverseStartFrom = $wall->count() - $start - 1;

        if ($reverseStartFrom < 0) {
            return new JsonResponse([
                'status' => 'nothingMore',
            ]);
        }

        $offset = $reverseStartFrom - static::LAZY_LOAD_PATCH_SIZE + 1;

        $threads = $wall->slice(
            $offset < 0 ? 0 : $offset,
            static::LAZY_LOAD_PATCH_SIZE + ($offset < 0 ? $offset : 0)
        );

        for ($i = count($threads) - 1; $i > -1; --$i) {
            $thread = $threads[$i];

            $threadJsonObject = [
                'id' => $thread->getId(),
                'posts' => [],
            ];

            foreach ($thread->getPosts() as $post) {
                $postUser = $post->getUser();

                $threadJsonObject['posts'][] = [
                    'user_id' => $postUser->getId(),
                    'post_id' => $post->getId(),
                    'username' => $postUser->getFirstName() . ' ' . $postUser->getLastName(),
                    'msg' => $post->getText(),
                    'ts' => $post->getTs(),
                ];
            }

            $responseBody['threads'][] = $threadJsonObject;
        }

        return new JsonResponse($responseBody);
    }
}
