<?php

namespace Network\WebBundle\Controller;

use Network\StoreBundle\Entity\Video;
use Network\StoreBundle\Entity\VideoReference;
use Network\UserBundle\Form\Type\VideoReferenceType;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RedirectResponse;

class VideoReferenceController extends Controller
{
    public function indexAction(Request $request)
    {
        $user = $this->getUser();
        $searchWord = $request->query->get('search');

        if (null === $user) {
            return $this->redirect($this->generateUrl('mainpage'));
        }

        $videoReferenceRep = $this->getDoctrine()->getRepository('NetworkStoreBundle:VideoReference');

        if (!empty($searchWord)) {
            $videoReferences = $videoReferenceRep->findByVideoReferenceName($searchWord);
        } else {
            $videoReferences = $videoReferenceRep->findBy(['user' => $user->getId()]);
        }

        $result = [];
        foreach ($videoReferences as $videoReference) {
            $media = $videoReference->getVideo()->getMedia();
            $metadata = $media->getProviderMetadata();
            $result[] = [
                'id'          => $videoReference->getId(),
                'html'        => $metadata['html'],
                'title'       => $videoReference->getName(),
                'description' => $videoReference->getDescription(),
                'thumbnail'   => $metadata['thumbnail_url'],
                'media'       => $media,
            ];

        }

        return $this->render('NetworkUserBundle:Video:video.html.twig', [
            'videos' => $result,
            'search' => $searchWord,
        ]);
    }

    public function addVideoReferenceAction(Request $request)
    {
        $user = $this->getUser();

        if (null === $user) {
            return $this->redirect($this->generateUrl('mainpage'));
        }

        $form = $this->createForm(new VideoReferenceType());
        $form->handleRequest($request);

        if ($form->isValid()) {
            $data = $form->getData();

            $video = new Video();
            $video->setMedia($data['media'])
                ->setUploaded(new \DateTime());

            $em = $this->getDoctrine()->getManager();
            $em->persist($video);
            $em->flush();

            $videoReference = new VideoReference();
            $videoReference->setVideo($video)
                  ->setUser($user);

            $description = $video->getMedia()->getDescription();

            if (null !== $data['description']) {
                $description = $data['description'];
            } else if (null === $description) {
                $description = ' ';
            }

            $videoReference->setDescription($description);

            if ($data['name']) {
                $videoReference->setName($data['name']);
            } else {
                $videoReference->setName($video->getMedia()->getName());
            }

            $em->persist($videoReference);
            $em->flush();

            $url = $this->generateUrl('videos');

            return new RedirectResponse($url);
        }

        return $this->render('NetworkUserBundle:Video:add_video.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    public function editVideoReferenceAction(Request $request)
    {
        $user = $this->getUser();

        if (null === $user) {
            return $this->redirect($this->generateUrl('mainpage'));
        }

        $videoId = $request->get('id');
        $videoReference = $this->getDoctrine()->getRepository('NetworkStoreBundle:VideoReference')->findOneBy(
            [
                'user' => $user->getId(),
                'id'   => $videoId
            ]
        );

        if (null === $videoReference) {
            return $this->redirect($this->generateUrl('videos'));
        }

        $form = $this->createForm(new VideoReferenceType());
        $form->add('media', 'hidden');
        $form->get('name')->setData($videoReference->getName());
        $form->get('description')->setData($videoReference->getDescription());
        $form->handleRequest($request);

        if ($form->isValid()) {

            $data = $form->getData();

            $videoReference->setName($data['name'])
                  ->setDescription($data['description']);

            $em = $this->getDoctrine()->getManager();
            $em->persist($videoReference);
            $em->flush();

            return new RedirectResponse($this->generateUrl('videos'));
        }

        return $this->render('NetworkUserBundle:Video:edit_video.html.twig', [
            'form'  => $form->createView(),
            'video' => $videoReference,
        ]);
    }
}
