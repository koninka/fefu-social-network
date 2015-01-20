<?php
namespace Network\StoreBundle\Controller;

use Network\StoreBundle\Entity\MP3File;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Network\StoreBundle\Entity\User;
use Network\StoreBundle\Entity\MP3Record;
use \GetId3\GetId3Core as GetId3;
use Symfony\Component\HttpFoundation\Response;

class FileController extends Controller
{
    const UPLOADED_MP3_NAME = 'mp3';
    const UPLOAD_DIR_NAME = 'uploads';
    const UPLOAD_MP3_DIR_NAME = 'mp3s';

    /**
     * Return the absolute directory path where uploaded
     * documents should be saved.
     *
     * @return string
     */
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

    public function mp3Action($file_id)
    {
        $record = $this->getDoctrine()
                     ->getRepository('NetworkStoreBundle:MP3Record')
                     ->find($file_id);

        $response = new BinaryFileResponse($record->getFile()->getPath());

        $response->headers->set('Content-Type', 'audio/mpeg');

        return $response;
    }

    public function uploadMp3Action(Request $request)
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
                'status' => 'somethingWrong'
            ]);
        }

        if ('mp3' !== pathinfo($uploadedFile->getClientOriginalName())['extension']) {
            return new JsonResponse([
                'status' => 'badFileExtension'
            ]);
        }

        $file = $uploadedFile->move(
            $this->getUploadRootDirForUser($user),
            $uploadedFile->getClientOriginalName()
                . ((string)time())
                . ((string)rand())
                . '.mp3'
        );

        $id3Analyzer = new GetId3();

        $song = null;
        $mp3data = $id3Analyzer->analyze($file->getPathName());

        if (array_key_exists('tags', $mp3data)) {
            $mp3tags =$mp3data['tags'];

            $flat = function (array $arr) {
                $newArr = [];

                foreach ($arr as $k => $v) {
                    $newArr[$k] = is_array($v) ? $v[0] : $v;
                }

                return $newArr;
            };

            $metadata = array_key_exists('id3v2', $mp3tags)
                        ? $flat($mp3tags['id3v2'])
                        : (
                            array_key_exists('id3v1', $mp3tags)
                            ? $flat($mp3tags['id3v1'])
                            : []
                        );

            $setDefaultValue = function (array &$arr, $key) {
                if (!array_key_exists($key, $arr)) {
                    $arr[$key] = 'unknown';
                }
            };

            $setDefaultValue($metadata, 'artist');
            $setDefaultValue($metadata, 'title');
            $setDefaultValue($metadata, 'genre');

            $song = $em->getRepository('NetworkStoreBundle:Song')
                       ->getSongByMetadata($metadata);
        }

        $mp3file = new MP3File();
        $mp3file->setPath($file->getPathName());

        $em->persist($mp3file);

        $mp3 = new MP3Record();

        $mp3file->addRecord($mp3);

        $mp3->setFile($mp3file)
            ->setUploaded(new \DateTime())
            ->setSong($song)
            ->addUser($user);

        $user->addMp3($mp3);

        $em->persist($mp3);
        $em->flush();

        $responseMetadata = [
            'title' => $metadata['title'],
            'artist' => $metadata['artist'],
            'file_id' => $mp3->getId(),
        ];

        if (
            null != $mp3->getSong()->getAlbum()
            && null !== $mp3->getSong()->getAlbum()->getPoster()
        ) {
            $responseMetadata['album_id'] = $mp3->getSong()->getAlbum()->getId();
        }

        return new JsonResponse([
            'status' => 'ok',
            'metadata' => $responseMetadata,
        ]);
    }

    public function deleteMp3Action(Request $request)
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

        $mp3->removeUser($user);
        $user->removeMp3($mp3);

        if (0 === $mp3->getUsers()->count()) {
            $em = $this->getDoctrine()->getManager();

            $file = $mp3->getFile();

            $file->removeRecord($mp3);

            if (0 === $file->getRecords()->count()) {
                unlink($file->getPath());
                $em->remove($file);
            }

            $em->remove($mp3);
            $em->flush();
        }

        return new JsonResponse([
            'status' => 'ok',
        ]);
    }

    public function posterAction($id)
    {
        $album = $this->getDoctrine()->getRepository('NetworkStoreBundle:Album')->find($id);

        $response =  new Response($album->getPoster());

        $response->headers->set('Content-Type', 'image/jpg');

        return $response;
    }
}