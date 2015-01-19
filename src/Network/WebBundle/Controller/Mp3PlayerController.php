<?php
namespace Network\WebBundle\Controller;

use Network\StoreBundle\Entity\MP3Record;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Network\StoreBundle\Controller\FileController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

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

    public function searchAction($by, $what)
    {
        $user = $this->getUser();
        if (null === $user) {
            return new JsonResponse([
                'status' => 'badUser',
            ]);
        }

        $repo = $this->getDoctrine()->getRepository('NetworkStoreBundle:MP3Record');

        $mp3s = $repo->searchRecords($by, $what);

        $responseContent = [
            'status' => 'ok',
            'results' => [],
        ];

        foreach ($mp3s as $mp3) {
            if (!$user->hasMp3InPlaylist($mp3)) {
                $media = [];
                $song = $mp3->getSong();

                $media['id'] = $mp3->getId();
                $media['artist'] = $song->getArtist();
                $media['title'] = $song->getTitle();

                if ($song->hasPoster()) {
                    $media['poster'] = $song->getAlbum()->getId();
                }

                $responseContent['results'][] = $media;
            }
        }

        return new JsonResponse($responseContent);
    }

    public function addAction($id)
    {
        $user = $this->getUser();
        if (null === $user) {
            return new JsonResponse([
                'status' => 'badUser',
            ]);
        }

        $mp3 = $this->getDoctrine()
                     ->getRepository('NetworkStoreBundle:MP3Record')
                     ->find($id);

        $content = [
            'status' => 'alreadyHas',
        ];

        if (!$user->hasMp3InPlaylist($mp3)) {
            $user->addMp3($mp3);
            $mp3->addUser($user);

            $song = $mp3->getSong();

            $content['status'] = 'ok';
            $content['artist'] = $song->getArtist();
            $content['title'] = $song->getTitle();
            $content['id'] = $mp3->getId();

            if ($song->hasPoster()) {
                $content['poster'] = $song->getAlbum()->getPoster();
            }

            $this->getDoctrine()->getManager()->flush();
        }

        return new JsonResponse($content);
    }

    public function editAction(Request $request)
    {
        $user = $this->getUser();
        if (null === $user) {
            return new JsonResponse([
                'status' => 'badUser',
            ]);
        }

        $data = json_decode($request->getContent(), true);

        if (
            count(array_intersect(['id', 'title', 'artist'], array_keys($data))) < 3
        ) {
            return new JsonResponse([
                'status' => 'badRequest'
            ]);
        }

        $mp3 = $this->getDoctrine()
                    ->getRepository('NetworkStoreBundle:MP3Record')
                    ->find($data['id']);

        if (!$user->hasMp3InPlaylist($mp3)) {
            return new JsonResponse([
                'status' => 'badUser',
            ]);
        }

        // TODO: add it to editable fields
        $data['genre'] = $mp3->getSong()->getGenre();

        $song = $this->getDoctrine()
                     ->getRepository('NetworkStoreBundle:Song')
                     ->getSongByMetadata($data);

        if ($mp3->getUsers()->count() > 1) {
            $user->removeMp3($mp3);

            $newMp3 = new MP3Record();

            $newMp3->addUser($user);
            $user->addMp3($newMp3);

            $newMp3->setFile($mp3->getFile());
            $newMp3->setUploaded($mp3->getUploaded());

            $this->getDoctrine()->getManager()->persist($newMp3);

            $mp3 = $newMp3;
        }

        $mp3->setSong($song);

        $this->getDoctrine()->getManager()->flush();

        $responseMetadata = [
            'id' => $mp3->getId(),
            'title' => $song->getTitle(),
            'artist' => $song->getArtist(),
            'old_id' => $data['id'],
        ];

        if ($song->hasPoster()) {
            $request['poster'] = $song->getAlbum()->getId();
        }

        return new JsonResponse([
            'status' => 'ok',
            'metadata' => $responseMetadata,
        ]);
    }
}