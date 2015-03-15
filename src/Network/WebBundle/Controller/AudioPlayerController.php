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

    public function removePlaylistAction($playlist_id)
    {
        $user = $this->getUser();
        if ($user === null) {
            return new JsonResponse([
                'status' => 'badUser',
            ]);
        }

        $repo = $this->getDoctrine()->getRepository('NetworkStoreBundle:Playlist');
        $playlist = $repo->find($playlist_id);
        if (empty($playlist) || $playlist->getUser()->getId() !== $user->getId()) {
            return new JsonResponse(['status' => 'badPlaylistId']);
        }

        $em = $this->getDoctrine()->getManager();
        $user->removePlaylist($playlist);
        $em->remove($playlist);
        $em->persist($user);
        $em->flush();
        return new JsonResponse(['status' => 'ok']);
    }

    public function addPlaylistAction($name)
    {
        $user = $this->getUser();
        if ($user === null) {
            return new JsonResponse([
                'status' => 'badUser',
            ]);
        }

        $playlists = $user->getPlaylists();

        $newPlaylist = new Playlist();
        $newPlaylist->setName($name);
        $user->addPlaylist($newPlaylist);
        $em = $this->getDoctrine()->getManager();
        $em->persist($user);
        $em->flush();
        $playlists = $user->getPlaylists();

        return new JsonResponse(['status' => 'ok']);
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

        $tracks = $repo->searchRecords($by, $what);

        $response = '{"status": "ok", "tracks": ';
        $serializer = $this->container->get('jms_serializer');
        $response = $response . $serializer->serialize($tracks, 'json');
        $response = $response . '}';

        return new Response(
            $response,
            Response::HTTP_OK,
            ['content-type' => 'application/json']
        );
    }

    public function editAction($audio_id, Request $request)
    {
        $user = $this->getUser();
        if (empty($user)) {
            return new JsonResponse([
                'status' => 'badUser',
            ]);
        }

        $audioTrack = $this->getDoctrine()
            ->getRepository('NetworkStoreBundle:AudioTrack')
            ->find($audio_id);

        if (empty($audioTrack)) {
            return new JsonResponse([
                'status' => 'badAudioId',
            ]);
        }

        $data = json_decode($request->getContent(), true);

        if (
            count(array_intersect(['title', 'artist'], array_keys($data))) === 0
        ) {
            return new JsonResponse([
                'status' => 'badRequest'
            ]);
        }

        // TODO: check if this user can edit this track
        $title = $data['title'];
        $artist = $data['artist'];
        if (!empty($title)) {
            $audioTrack->setTitle($title);
        }
        if (!empty($artist)) {
            $audioTrack->setArtist($artist);
        }

        $em = $this->getDoctrine()->getManager();
        $em->persist($audioTrack);
        $em->flush();

        return new JsonResponse([
            'status' => 'ok',
        ]);
    }

    public function pushTrackToPlaylistAction($playlist_id, $track_id)
    {
        // TODO: decide if we should clone on push
        $playlist = $this->getDoctrine()
            ->getRepository('NetworkStoreBundle:Playlist')
            ->find($playlist_id);

        if (empty($playlist)) {
            return new JsonResponse([
                'status' => 'badPlaylist',
            ]);
        }

        $track = $this->getDoctrine()
            ->getRepository('NetworkStoreBundle:AudioTrack')
            ->find($track_id);

        if (empty($track)) {
            return new JsonResponse([
                'status' => 'badTrack',
            ]);
        }

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

    public function deleteAudioFromPlaylistAction($audio_id, $playlist_id)
    {
        $user = $this->getUser();
        if (empty($user)) {
            return new JsonResponse([
                'status' => 'badUser',
            ]);
        }

        $playlist = $this->getDoctrine()
            ->getRepository('NetworkStoreBundle:Playlist')
            ->find($playlist_id);

        if (empty($playlist) || $playlist->getUser()->getId() !== $user->getId()) {
            return new JsonResponse([
                'status' => 'badPlaylist',
            ]);
        }

        $playlistItem = $this->getDoctrine()
            ->getRepository('NetworkStoreBundle:PlaylistItem')
            ->findByPlaylistAndTrack($playlist_id, $audio_id);

        if (empty($playlistItem)) {
            return new JsonResponse([
                'status' => 'trackNotInList',
            ]);
        }

        $em = $this->getDoctrine()->getManager();
        $em->remove($playlistItem);
        $em->flush();

        return new JsonResponse([
            'status' => 'ok',
        ]);
    }
}
