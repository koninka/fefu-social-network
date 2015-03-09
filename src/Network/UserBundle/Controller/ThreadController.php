<?php

namespace Network\UserBundle\Controller;

use Network\StoreBundle\DBAL\ThreadEnumType;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use FOS\UserBundle\Model\UserInterface;
use Network\UserBundle\Form\Type\ContactInfoType;
use Network\UserBundle\Form\Type\CommunityType;
use Network\UserBundle\Form\Type\CreateCommunityType;
use Network\StoreBundle\DBAL\RelationshipStatusEnumType;
use Symfony\Component\Config\Definition\Exception\Exception;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Network\StoreBundle\Entity\Community;
use Network\StoreBundle\Entity\UserCommunity;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Network\StoreBundle\DBAL\RoleCommunityEnumType;
use Network\StoreBundle\DBAL\TypeCommunityEnumType;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\DependencyInjection\Exception\ServiceNotFoundException;

/**
 * Class ThreadController
 *
 * @package Network\UserBundle\Controller
 */
class ThreadController extends Controller
{

    /**
     * Show messenger page
     * @param Request $request
     *
     * @return Response
     */
    public function showIMAction(Request $request)
    {
        $user = $this->getUser();
        if (empty($user)) {
            return $this->redirect($this->generateUrl('mainpage'));
        }
        $partnerId = $request->query->get('partnerId');
        $partnerName = null;
        if ($partnerId != null && $partnerId != $user->getId()) {
            $partner = $this->getDoctrine()->getRepository('NetworkStoreBundle:User')->find($partnerId);
            if ($partner) {
                $partnerName = $partner->getFirstName() . ' ' . $partner->getLastName();
            }
        }

        return $this->render(
            'NetworkUserBundle:Profile:im.html.twig',
            [
                'user_id' => $user->getId(),
                'partnerName' => $partnerName,
                'partnerId' => $partnerId
            ]
        );
    }

    /**
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function postAction(Request $request)
    {
        $imService = $this->get('network.store.im_service');
        $user = $this->getUserAndCheckAccess();
        $text = $request->request->get('text', '');
        $files = $request->request->get('files');

        if (trim($text) == '') {
            return $this->errorJsonResponse('field \'text\' is empty');
        }

        $threadId = $request->request->get('threadId');
        $res = [];

        if ($threadId != null) {
            $thread = $imService->getThreadByIdAndUserIdOrThrow($threadId, $user->getId());
        } else {
            $recipientIds = $request->request->get('recipientId');

            if (!is_array($recipientIds) || empty($recipientIds)) {
                return $this->errorJsonResponse('field \'recipientId\' is empty or not array_type');
            }

            $recipientUsers = $this->getDoctrine()
                ->getRepository('NetworkStoreBundle:User')
                ->findBy(['id' => $recipientIds]);

            if (!$recipientUsers) {
                return $this->errorJsonResponse('users with given ids not found');
            }

            $recipientUsers = new ArrayCollection($recipientUsers);
            $recipientUsers->removeElement($user); // to be sure that client does not send yourself

            if ($recipientUsers->isEmpty()) {
                return $this->errorJsonResponse('users with given ids not found');
            }

            if ($recipientUsers->count() == 1) {
                $rUser = $recipientUsers[0];
                $res['topic'] = $rUser->getFirstName() . ' ' . $rUser->getLastName();
            }

            $topic = $request->request->get('topic', '');
            $thread = $imService->createDialogOrConference($user, $recipientUsers, $topic);

            if (!array_key_exists('topic', $res)) {
                $res['topic'] = $thread->getTopic();
            }
        }

        $post = $imService->createPost($user, $thread, $text, $files);

        $res['threadId'] = $thread->getId();

        $postFiles = [];

        foreach ($post->getFiles()->toArray() as $file) {
            $postFiles[] = [
                'id'   => $file->getId(),
                'name' => $file->getName(),
                'hash' => $file->getHash()
            ];
        }

        $formatter =  $this->container->get('sonata.formatter.pool');

        $postResult = [
            'id'        => $post->getId(),
            'ts'        => $post->getTs(),
            'text'      => $formatter->transform('markdown', $text),
            'author'    => $post->getUser()->getFirstName() .' '. $post->getUser()->getLastName(),
            'postFiles' => $postFiles,
            'userId'    => $post->getUser()->getId()
        ];

        $res['post'] = $postResult;

        return new JsonResponse($res);
    }

    /**
     * @param Request $request
     *
     * @return JsonResponse
     * @throws \Exception
     */
    public function threadListAction(Request $request)
    {
        $user = $this->getUserAndCheckAccess();
        $threadRepo = $this->getDoctrine()->getRepository('NetworkStoreBundle:Thread');
        $threads = $threadRepo->getThreadListForUser($user->getId());
        $response = new JsonResponse();
        $response->setData($threads);

        return $response;
    }

    /**
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function threadAction(Request $request)
    {
        $user = $this->getUserAndCheckAccess();
        $threadId = $request->request->get('id');

        if ($threadId == null) {
            return $this->errorJsonResponse('Invalid thread id');
        }

        $this->checkAccessToThread($threadId, $user->getId());
        $threadRepo = $this->getDoctrine()->getRepository('NetworkStoreBundle:Thread');

        $posts = $this->getDoctrine()
            ->getRepository('NetworkStoreBundle:Post')
            ->findBy(['thread' => $threadId]);

        $unreadPosts = $threadRepo->getUnreadPostsByUser($threadId, $user->getId());

        $formatter =  $this->container->get('sonata.formatter.pool');
        $postResult = [];

        foreach ($posts as $post) {
            $postFiles = [];

            foreach ($post->getFiles()->toArray() as $file) {
                $postFiles[] = [
                    'id'   => $file->getId(),
                    'name' => $file->getName(),
                    'hash' => $file->getHash()
                ];
            }

            $postResult[] = [
                'id'        => $post->getId(),
                'ts'        => $post->getTs(),
                'text'      => $formatter->transform('markdown', $post->getText()),
                'author'    => $post->getUser()->getFirstName() .' '. $post->getUser()->getLastName(),
                'postFiles' => $postFiles,
                'userId'    => $post->getUser()->getId()
            ];
        }

        return new JsonResponse([
            'posts'       => $postResult,
            'unreadPosts' => $unreadPosts,
            'selfId'      => $user->getId()
        ]);
    }

    /**
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function getFriendsJsonAction(Request $request)
    {
        $user = $this->getUserAndCheckAccess();
        $limit = $request->get('limit', 10);
        $page = $request->get('page', 1);
        $q = $request->get('query', '');
        $threadId = $request->get('threadId');
        $notInThreadFilter = null;
        if ($threadId != null) {
            $notInThreadFilter = function ($queryBuilder) use ($threadId) {
                return $queryBuilder
                    ->andWhere(
                        'NOT EXISTS(
                        SELECT ut FROM NetworkStoreBundle:UserThread ut
                        WHERE ut.user = p AND ut.thread = :filter_thread_id
                        )'
                    )
                    ->setParameter('filter_thread_id', $threadId);
            };
        }
        $rels = $this->getDoctrine()->getRepository('NetworkStoreBundle:Relationship');
        $paginator = $this->get('network.store.paginator');
        $friends = $rels->getPaginatedAndFilteredFriends(
            $user->getId(),
            $paginator,
            $page,
            $limit,
            $q,
            $notInThreadFilter
        );

        return new JsonResponse($friends);
    }

    /**
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function readPostsAction(Request $request)
    {
        $user = $this->getUserAndCheckAccess();
        $threadId = $request->request->get('threadId');
        $count = $request->request->get('count', 0);
        $imService = $this->get('network.store.im_service');

        return $imService->readPosts($user->getId(), $threadId, $count);

    }

    /**
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function addUserToConferenceAction(Request $request)
    {
        $user = $this->getUserAndCheckAccess();
        $conferenceId = $request->request->get('conferenceId');
        $newUserId = $request->request->get('userId');
        if ($conferenceId == null) {
            return $this->errorJsonResponse('Invalid conference Id');
        }
        if ($newUserId == null) {
            return $this->errorJsonResponse('Invalid user Id');
        }
        $imService = $this->get('network.store.im_service');
        $thread = $imService->getConferenceByIdAndUserIdOrThrow($conferenceId, $user->getId());
        $newUser = $this->getDoctrine()->getRepository('NetworkStoreBundle:User')->find($newUserId);
        if ($newUser == null) {
            return $this->errorJsonResponse('User with id ' . $newUserId . ' not found');
        }
        $thread->addUser($newUser, $user->getId());
        $manager = $this->getDoctrine()->getManager();
        $manager->persist($thread);
        $manager->flush();

        return new JsonResponse(['conferenceId' => $conferenceId, 'userId' => $newUserId]);
    }

    /**
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function changeTopicAction(Request $request)
    {
        $user = $this->getUserAndCheckAccess();
        $newTopic = $request->request->get('topic', '');
        if (trim($newTopic) == '') {
            return $this->errorJsonResponse('field \'topic\' is empty');
        }
        $conferenceId = $request->request->get('conferenceId');
        if ($conferenceId == null) {
            return $this->errorJsonResponse('Invalid conference id');
        }
        $imService = $this->get('network.store.im_service');
        $thread = $imService->getConferenceByIdAndUserIdOrThrow($conferenceId, $user->getId());
        $thread->setTopic($newTopic);
        $manager = $this->getDoctrine()->getManager();
        $manager->persist($thread);
        $manager->flush();

        return new JsonResponse(['conferenceId' => $conferenceId, 'topic' => $newTopic]);
    }

    /**
     * @param Request $request
     *
     * @return JsonResponse
     * @throws \Symfony\Component\Security\Acl\Exception\Exception
     */
    public function kickUserAction(Request $request)
    {
        $user = $this->getUserAndCheckAccess();
        $challengerId = $request->request->get('userId');
        if ($challengerId == null) {
            return $this->errorJsonResponse('Invalid user id');
        }
        $conferenceId = $request->request->get('conferenceId');
        if ($conferenceId == null) {
            return $this->errorJsonResponse('Invalid conference id');
        }
        $this->checkAccessToThread($conferenceId, $user->getId());

        return $this->get('network.store.im_service')->kickUserFromConference($user->getId(), $conferenceId, $challengerId);
    }

    /**
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function leaveConferenceAction(Request $request)
    {
        $user = $this->getUserAndCheckAccess();
        $conferenceId = $request->request->get('conferenceId');
        if ($conferenceId == null) {
            return $this->errorJsonResponse('Invalid conference id');
        }

        return $this->get('network.store.im_service')->kickUserFromConference($user->getId(), $conferenceId, $user->getId());
    }
    /**
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function getUsersInThreadAction(Request $request)
    {
        $user = $this->getUserAndCheckAccess();
        $threadId = $request->request->get('threadId');
        $imService = $this->get('network.store.im_service');
        $users = $imService->getUsersInThreadByIdAndUserIdOrThrow($threadId, $user->getId());
        if ($imService->isThreadOwner($threadId, $user->getId())) {
            $canBeKicked = array_keys($users);
        } else {
            $canBeKicked = $imService->getInvitedUsersByUserInThread($threadId, $user->getId());
        }

        return new JsonResponse(['users' => $users, 'userId' => $user->getId(), 'canBeKicked' => $canBeKicked]);
    }

    static protected function errorJsonResponse($msg)
    {
        return new JsonResponse(['error' => $msg]);
    }

    protected function getUserAndCheckAccess()
    {
        $user = $this->getUser();
        $this->checkAccess($user);

        return $user;
    }
    protected function checkAccess($user)
    {
        //TODO: push it in base class
        if (!is_object($user) || !$user instanceof UserInterface) {
            throw new AccessDeniedException('This user does not have access to this section.');
        }
    }

    protected function checkAccessToThread($threadId, $userId)
    {
        $threadRepo = $this->getDoctrine()->getRepository('NetworkStoreBundle:Thread');
        if (!$threadRepo->checkPermission($threadId, $userId)) {
            throw new AccessDeniedException('This user does not have access to this section.');
        }
    }
}
