<?php

namespace Network\StoreBundle\Controller;

use Network\StoreBundle\Entity\AudioTrack;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Network\StoreBundle\Entity\User;
use \GetId3\GetId3Core as GetId3;
use Symfony\Component\HttpFoundation\Response;

class AudioTrackController extends Controller
{
    const UPLOAD_DIR_NAME = 'uploads';
    protected function getUploadRootDir()
    {
        return __DIR__
                . '/../../../../web/'
                . static::UPLOAD_DIR_NAME
                . '/'
            ;
    }
    /**
     * Return the absolute directory path where uploaded
     * user's documents should be saved.
     *
     * @param User $user
     * @return string
     */
    protected function getUploadRootDirForUser(User $user)
    {
        return $this->getUploadRootDir() . $user->getEmail() . '/';
    }

    private function analyzeAudioFile($path)
    {
        $id3Analyzer = new GetId3();

        $id3data = $id3Analyzer->analyze($path);

        $metadata = [];

        if (array_key_exists('tags', $id3data)) {
            $tags =$id3data['tags'];

            $flat = function (array $arr) {
                $newArr = [];

                foreach ($arr as $k => $v) {
                    $newArr[$k] = is_array($v) ? $v[0] : $v;
                }

                return $newArr;
            };

            $metadata = array_key_exists('id3v2', $tags)
                ? $flat($tags['id3v2'])
                : (
                array_key_exists('id3v1', $tags)
                    ? $flat($tags['id3v1'])
                    : []
                );

        }

        return $metadata;
    }

    public function downloadAction($id)
    {
        $audioTrack = $this->getDoctrine()
            ->getRepository('NetworkStoreBundle:AudioTrack')
            ->find($id);

        $response = new BinaryFileResponse($this->getUploadRootDirForUser($audioTrack->getUser())
            . $audioTrack->getFileHash());
        // TODO: store mime type in AudioRecord and use it here?
        $response->headers->set('Content-Type', 'audio/mpeg');

        return $response;
    }

    public function uploadAction(Request $request)
    {
        $user = $this->getUser();
        $em = $this->getDoctrine()->getManager();

        if (null === $user) {
            return new JsonResponse([
                'status' => 'badUser',
            ]);
        }

        $uploadedFile = $request->files->get('file');

        if (null === $uploadedFile) {
            return new JsonResponse([
                'status' => 'fileNotPresent'
            ]);
        }

        $mimeType = $uploadedFile->getMimeType();
        if (!preg_match("/^audio/", $mimeType) &&
            $mimeType !== 'application/ogg') {
            return new JsonResponse([
                'status' => 'invalidMimeType',
                'mime' => $mimeType,
            ]);
        }

        $filename = pathinfo($uploadedFile->getClientOriginalName())['filename'];
        if (preg_match('/(?P<artist>.*?)[-–—](?P<title>.*)/u', $filename, $matches)) {
            $artist = trim($matches['artist']);
            $title = trim($matches['title']);
        } else {
            $artist = '';
            $title = trim($filename);
        }

        $hash = hash('sha256', file_get_contents($uploadedFile->getRealPath()));
        $file = $uploadedFile->move(
            $this->getUploadRootDirForUser($user),
            $hash
        );

        $metadata = $this->analyzeAudioFile($file->getPathName());
        $artist = array_key_exists('artist', $metadata) ? $metadata['artist'] : $artist;
        $title = array_key_exists('title', $metadata) ? $metadata['title'] : $title;

        $audioTrack = new AudioTrack();
        $user->addUploadedTrack($audioTrack);
        $audioTrack->setTitle($title)
            ->setArtist($artist)
            ->setUploadDate(new \DateTime())
            ->setfileHash($hash);

        $em->persist($audioTrack);
        $em->flush();

        return new JsonResponse([
            'status' => 'ok',
            'mime' => $mimeType,
            'hash' => $hash,
            'title' => $title,
            'artist' => $artist,
            'id' => $audioTrack->getId(),
        ]);
    }

    public function deleteAction(Request $request)
    {
        $user = $this->getUser();
        if (null === $user) {
            return new JsonResponse([
                'status' => 'badUser',
            ]);
        }

        $data = json_decode($request->getContent(), true);

        if (!array_key_exists('id', $data)) {
            return new JsonResponse([
                'status' => 'badId'
            ]);
        }

        $id = $data['id'];
        $mp3 = $this->getDoctrine()
            ->getRepository('NetworkStoreBundle:MP3Record')
            ->find($id);

        if (null === $mp3) {
            return new JsonResponse([
                'status' => 'badId'
            ]);
        }

        $em = $this->getDoctrine()->getManager();

        $mp3->removeUser($user);
        $user->removeMp3($mp3);

        if (0 === $mp3->getUsers()->count()) {

            $file = $mp3->getFile();

            $file->removeRecord($mp3);

            if (0 === $file->getRecords()->count()) {
                unlink($file->getPath());
                $em->remove($file);
            }

            $em->remove($mp3);
        }

        $em->flush();

        return new JsonResponse([
            'status' => 'ok',
            'id' => $id
        ]);
    }
}
