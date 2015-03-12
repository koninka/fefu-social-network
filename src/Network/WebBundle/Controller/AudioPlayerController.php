<?php

namespace Network\WebBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;

use Network\StoreBundle\Entity\Playlist;
use Network\StoreBundle\Entity\AudioTrack;
use Network\StoreBundle\Entity\PlaylistItem;

class AudioPlayerController extends Controller
{
    public function viewPlayerAction()
    {
        $user = $this->getUser();
        if ($user === null) {
            return $this->redirect($this->generateUrl('mainpage'));
        }

        return $this->render('NetworkWebBundle:AudioPlayer:audio_player.html.twig');
    }

    public function getAllMyPlaylistsAction()
    {
        $user = $this->getUser();
        if ($user === null) {
            return new JsonResponse([
                'status' => 'badUser',
            ]);
        }

        $repo = $this->getDoctrine()->getRepository('NetworkStoreBundle:Playlist');
        $playlists = $user->getPlaylists();
        if ($playlists->isEmpty()) {
            // default playlist for each user is created here
            $newPlaylist = new Playlist();
            $newPlaylist->setName("music");
            $user->addPlaylist($newPlaylist);
            $em = $this->getDoctrine()->getManager();
            $em->persist($user);
            $em->flush();
            $playlists = $user->getPlaylists();
        }

        $response = '{"status": "ok", "playlists": ';
        $serializer = $this->container->get('jms_serializer');
        $response = $response . $serializer->serialize($playlists, 'json');
        $response = $response . '}';

    $logger = $this->get('logger');
    $logger->info($response);

        return new Response(
            $response,
            Response::HTTP_OK,
            ['content-type' => 'application/json']
        );
    }

    public function searchAction($by, $what)
    {
        $user = $this->getUser();
        if (null === $user) {
            return new JsonResponse([
                'status' => 'badUser',
            ]);
        }

        $repo = $this->getDoctrine()->getRepository('NetworkStoreBundle:AudioTrack');

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
                     ->getRepository('NetworkStoreBundle:AudioTrack')
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
                    ->getRepository('NetworkStoreBundle:AudioTrack')
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

            $newMp3 = new AudioTrack();

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

    public function pushTrackToPlaylistAction($playlist_id, $track_id)
    {
        $playlist = $this->getDoctrine()
            ->getRepository('NetworkStoreBundle:Playlist')
            ->find($playlist_id);

        $track = $this->getDoctrine()
            ->getRepository('NetworkStoreBundle:AudioTrack')
            ->find($track_id);

        $item = new PlaylistItem();
        $playlist->addItem($item);
        $track->addPlaylistItem($item);
        $item->setRank($playlist->getItems()->count());

        $em = $this->getDoctrine()->getManager();
        $em->persist($item);
        $em->flush();

        return new JsonResponse([
            'status' => 'ok',
        ]);
    }
}