<?php

namespace Network\WebBundle\Controller;


use Network\StoreBundle\Form\Type\JobsCollectionType;
use Network\WebBundle\NetworkWebBundle;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Network\StoreBundle\Form\Type\UserType;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Doctrine\Common\Collections\ArrayCollection;
use Network\WebBundle\Controller\Mp3FileController;

class UserController extends Controller
{
    const MAX_COUNT = 15;

    private $searchData = [
        'post' => [
            'repo' => 'NetworkStoreBundle:JobPost',
        ],
    ];

    public function jobsAction(Request $request)
    {
        $user = $this->getUser();
        $em = $this->getDoctrine()->getManager();
        if (null === $user) {
            return $this->redirect($this->generateUrl('mainpage'));
        }

        $originalJobs = new ArrayCollection();
        foreach ($user->getJobs() as $job) {
            $originalJobs->add($job);
        }

        $form = $this->createForm(new JobsCollectionType(), $user);

        $form->handleRequest($request);

        if ($form->isValid()) {
            $newJobs = $user->getJobs();

            foreach ($originalJobs as $oldJob) {
                if (!$newJobs->contains($oldJob)) {
                    $newJobs->removeElement($oldJob);
                    $em->remove($oldJob);
                }
            }

            foreach ($newJobs as $actualJob) {
                $actualJob->setUser($user);
            }

            $em->flush();
        }

        return $this->render('NetworkWebBundle:User:profile_jobs.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    public function jsonAction(Request $request)
    {
        $data = json_decode($request->getContent(), true);

        $what = $data['what'];
        $by = $data['by'];
        $val = $data['val'];
        $response = [];

        if (null === $what || null === $by || !array_key_exists($what, $this->searchData)) {
            $response['result'] = 'badParams';
        } else {
            $repo = $this->getDoctrine()
                         ->getRepository($this->searchData[$what]['repo']);

            $entities = $repo->createQueryBuilder('i')
                ->where("i.$by LIKE :cond")
                ->setMaxResults(self::MAX_COUNT)
                ->setParameter('cond', "$val%")
                ->getQuery()
                ->getResult();

            $serializer = $this->container->get('jms_serializer');
            $response['data'] = [];

            foreach($entities as $entity) {
                $response['data'][] = $serializer->serialize($entity, 'json');
            }

            $response['result'] = 'ok';
        }

        return new JsonResponse($response);
    }

    public function sendFriendshipRequestAction($id)
    {
        return new JsonResponse([
            'status' => $this->get('network_store.relationship_manager')->sendFriendshipRequest($id)
        ]);
    }

    public function deleteFriendshipRequestAction($id)
    {
        return new JsonResponse([
            'status' =>  $this->get('network_store.relationship_manager')->deleteFriendship($id),
        ]);
    }

    public function deleteFriendshipSubscriptionAction($id)
    {
        return new JsonResponse([
            'status' => $this->get('network_store.relationship_manager')->deleteFriendshipSubscription($id),
        ]);
    }

}
