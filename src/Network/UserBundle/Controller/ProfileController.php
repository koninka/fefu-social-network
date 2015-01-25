<?php

/*
 * This file is part of the FOSUserBundle package.
 *
 * (c) FriendsOfSymfony <http://friendsofsymfony.github.com/>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Network\UserBundle\Controller;

use FOS\UserBundle\Controller\ProfileController as BaseController;
use FOS\UserBundle\Model\UserInterface;
use Network\UserBundle\Form\Type\ContactInfoType;
use Network\StoreBundle\DBAL\RelationshipStatusEnumType;
use Symfony\Component\Security\Acl\Exception\Exception;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Network\StoreBundle\Entity\Thread;
use Network\StoreBundle\Entity\Post;


class ProfileController extends BaseController
{
    use ProfileTrait;

    public function showAction()
    {
        $user = $this->getUser();

        if (!is_object($user) || !$user instanceof UserInterface) {
            throw new AccessDeniedException('This user does not have access to this section.');
        }

        return $this->redirect($this->generateUrl('user_profile', ['id' => $user->getId()]));
    }

    public function profileAction($id, Request $request)
    {
        $user = $this->getDoctrine()->getRepository('NetworkStoreBundle:User')->find($id);
        if (empty($user)) {
            return $this->redirect($this->generateUrl('mainpage'));
        }

        $rels = $this->getDoctrine()->getRepository('NetworkStoreBundle:Relationship');

        $isCurUser = false;
        $fsStatus = RelationshipStatusEnumType::FS_NONE;
        if ($this->get('security.context')->isGranted('ROLE_USER')) {
            $curUser = $this->getUser();
            $isCurUser = ($curUser->getId() === $user->getId());
            $fsStatus = $rels->getRelationshipForUser($curUser->getId(), $user->getId())->getStatus();
        }

        return $this->render('NetworkUserBundle:Profile:show.html.twig', [
            'user' => $user,
            'rl_status' => $fsStatus,
            'is_cur_user' => $isCurUser
        ]);
    }

    public function showFriendsAction($id)
    {
        $user = $this->getDoctrine()->getRepository('NetworkStoreBundle:User')->find($id);
        if (empty($user)) {
            return $this->redirect($this->generateUrl('mainpage'));
        }

        $rels = $this->getDoctrine()->getRepository('NetworkStoreBundle:Relationship');

        $isCurUser = false;
        if ($this->get('security.context')->isGranted('ROLE_USER')) {
            $curUser = $this->getUser();
            $isCurUser = ($curUser->getId() === $user->getId());
        }

        return $this->render('NetworkUserBundle:Profile:friends.html.twig', [
            'is_cur_user' => $isCurUser,
            'user_id' => $user->getId(),
            'friends' => $rels->findFriendsForUser($user->getId()),
            'subscribers' => $rels->findSubscribersForUser($user->getId()),
            'subscribed_on' => $rels->findSubscribedOnForUser($user->getId())
        ]);
    }

    public function showProfileFriendsAction()
    {
        $user = $this->getUser();
        if (empty($user)) {
            return $this->redirect($this->generateUrl('mainpage'));
        }

        return $this->showFriendsAction($user->getId());
    }

    public function manageFriendshipRequestsAction()
    {
        $rels = $this->getDoctrine()->getRepository('NetworkStoreBundle:Relationship');

        return $this->render('NetworkUserBundle:Profile:manage_requests.html.twig', [
            'friendship_requests' => $rels->findFriendshipRequestsForUser($this->getUser()->getId())
        ]);
    }

    public function contactAction(Request $request)
    {
        $user = $this->getUser();
        if (!is_object($user) || !$user instanceof UserInterface) {
            throw new AccessDeniedException('This user does not have access to this section.');
        }
        $formContact = $this->container->get('form.factory')->create(
            new ContactInfoType(),
            $user->getContactInfo()
        );
        $formContact->handleRequest($request);

        if ($formContact->isValid()) {
            $em = $this->container->get('doctrine')->getManager();
            $em->persist($user->getContactInfo());
            $em->flush();

            return $this->redirect($this->generateUrl('user_profile', ['id' => $user->getId()]));
        }

        return $this->render('FOSUserBundle:Profile:contact.html.twig', [
            'form' =>  $formContact->createView()
        ]);
    }

    public function showIMAction(Request $request)
    {
        $user = $this->getUser();
        if (empty($user)) {
            return $this->redirect($this->generateUrl('mainpage'));
        }
        $partnerId = $request->query->get('partnerId', null);
        $partnerName = null;
        if ($partnerId != null && $partnerId != $user->getId()) {
            $partner = $this->getDoctrine()->getRepository('NetworkStoreBundle:User')->find($partnerId);
            if ($partner) {
                $partnerName = $partner->getFirstName() . ' ' . $partner->getLastName();
            }
        }

        return $this->render('NetworkUserBundle:Profile:im.html.twig', [
            'user_id' => $user->getId(),
            'partnerName' => $partnerName,
            'partnerId' => $partnerId,
        ]);
    }

    public function postAction(Request $request)
    {
        $imService = $this->get('network.store.im_service');
        $user = $this->getUser();
        $data = json_decode($request->getContent(), true);
        if ($data == null || !array_key_exists('text', $data) || trim($data['text']) == '') {
            return new JsonResponse(['error' => 'field \'text\' is empty']);
        }
        if (array_key_exists('threadId', $data)) {
            $threadRepo = $this->getDoctrine()->getRepository('NetworkStoreBundle:Thread');
            $thread = $threadRepo->getThreadByIdAndUser($data['threadId'], $user->getId());
            if ($thread == null) {
                throw new AccessDeniedException('This user does not have access to this section.');
            }
        } else {
            if (!array_key_exists('recipientId', $data) || !is_numeric($data['recipientId'])) {
                return new JsonResponse(['error' => 'field \'recipientId\' is not numeric']);
            }
            $recipientUser = $this->getDoctrine()
                                  ->getRepository('NetworkStoreBundle:User')
                                  ->find($data['recipientId']);

            if (!$recipientUser) {
                return new JsonResponse(['error' => $data['recipientId'] . ' not found']);
            }
            if ($recipientUser->getId() == $user->getId()) {
                return new JsonResponse(['error' => 'unable to write to yourself']);
            }

            $thread = $this->getDoctrine()
                           ->getRepository('NetworkStoreBundle:Thread')
                           ->findByUsers($user->getId(), $recipientUser->getId());

            if (!$thread or count($thread) == 0) {
                // there's no 1x1 thread between this pair of users
                // so we're creating a new one
                $thread = new Thread();
                $thread->setTopic('no topic');
                $imService->persistThread($thread); //because of foreign key error
                $thread->addUser($user)
                       ->addUser($recipientUser);

                $imService->persistThread($thread);

            } elseif (count($thread) > 1) {
                throw new Exception('SERVER ERROR: 2 dialogs between 2 persons');
            } else {
                $thread = $thread[0];
            }
        }

        $oldTimeZone = date_default_timezone_get();
        date_default_timezone_set("UTC");

        $post = new Post();
        $post->setText($data['text'])
             ->setTs(new \DateTime('now'))
             ->setUser($user)
             ->setThread($thread);
        $thread->incUnreadPosts($user);

        $imService->persistPost($post);

        date_default_timezone_set($oldTimeZone);

        return new JsonResponse(['threadId' => $thread->getId()]);
    }

    public function threadListAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $user = $this->getUser();
        $data = json_decode($request->getContent(), true);

        $threadRepo = $this->getDoctrine()->getRepository('NetworkStoreBundle:Thread');
        $threads = $threadRepo->getThreadListForUser($user->getId());

        $response = new JsonResponse();
        $response->setData($threads);

        return $response;
    }

    public function threadAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $user = $this->getUser();

        $data = json_decode($request->getContent(), true);
        if ($data == null || !array_key_exists('id', $data)) {
            return new JsonResponse(['error' => 'field "id" is empty']);
        }
        $threadId = $data['id'];
        $threadRepo = $this->getDoctrine()->getRepository('NetworkStoreBundle:Thread');
        if (!$threadRepo->checkPermission($threadId, $user->getId())) {
            throw new AccessDeniedException('This user does not have access to this section.');
        }
        $posts = $this->getDoctrine()
                      ->getRepository('NetworkStoreBundle:Post')
                      ->getThreadPosts($threadId);
        $unreadPosts = $threadRepo->getUnreadPostsByUser($threadId, $user->getId());

        $formatter =  $this->container->get('sonata.formatter.pool');
        foreach ($posts as &$post) {
            $post['text'] = $formatter->transform('markdown', $post['text']);
        }

        return new JsonResponse(['posts' => $posts, 'unreadPosts' => $unreadPosts, 'selfId' => $user->getId()]);
    }

    public function getFriendsJsonAction(Request $request)
    {
        $user = $this->getUser();
        if (!is_object($user) || !$user instanceof UserInterface) {
            throw new AccessDeniedException('This user does not have access to this section.');
        }
        $limit = $request->get('limit', 10);
        $page = $request->get('page', 1);
        $q = $request->get('query', '');
        $rels = $this->getDoctrine()->getRepository('NetworkStoreBundle:Relationship');
        $paginator = $this->get('network.store.paginator');
        $friends = $rels->getPaginatedFriends($user->getId(), $paginator, $page, $limit, $q);

        return new JsonResponse($friends);
    }

    public function readPostsAction(Request $request)
    {
        $user = $this->getUser();
        if (!is_object($user) || !$user instanceof UserInterface) {
            throw new AccessDeniedException('This user does not have access to this section.');
        }
        $data = json_decode($request->getContent(), true);
        if ($data == null || !array_key_exists('threadId', $data)) {
            return new JsonResponse(['error' => 'threadId field is missing']);
        }
        $threadId = $data['threadId'];
        $count = 0;
        if (array_key_exists('count', $data)) {
            $count = $data['count'];
        }
        $userThreadRep = $this->getDoctrine()->getRepository('NetworkStoreBundle:UserThread');
        $userThread = $userThreadRep->findByUserAndThread($user->getId(), $threadId);
        if ($userThread == null) {
            return new JsonResponse(['error' => 'thread not found']);
        }
        $userThread->decUnreadPosts($count);
        $this->get('network.store.im_service')->persistUserThread($userThread);

        return new JsonResponse(['count' => $count]);
    }

    public function showProfileAlbumsAction()
    {
        $user = $this->getUser();
        if (empty($user)) return $this->redirect($this->generateUrl('mainpage'));

        return $this->showAlbumsAction($user->getId());
    }

    public function showAlbumsAction($id)
    {
        $user = $this->getDoctrine()->getRepository('NetworkStoreBundle:User')->find($id);
        if (empty($user)) return $this->redirect($this->generateUrl('mainpage'));

        $albums = $this->getDoctrine()->getRepository('NetworkStoreBundle:UserGallery');

        $isCurUser = false;
        if ($this->get('security.context')->isGranted('ROLE_USER')) {
            $curUser = $this->getUser();
            $isCurUser = ($curUser->getId() === $user->getId());
        }

        return $this->render('NetworkUserBundle:Albums:albums.html.twig', [
            'user_id' => $user->getId(),
            'is_cur_user' => $isCurUser,
            'albums' => $albums->findAlbumsForUser($user->getId()),
        ]);
    }

}
