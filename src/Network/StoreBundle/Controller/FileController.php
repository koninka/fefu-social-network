<?php
namespace Network\StoreBundle\Controller;

use Network\StoreBundle\Entity\MP3File;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Network\StoreBundle\Entity\User;
use Network\StoreBundle\Entity\PostFile;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Validator\Constraints\Null;

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
                'status' => 'fileNotFound'
            ]);
        }

        $postfile = new PostFile();

        $postfile->setName($uploadedFile->getClientOriginalName())
            ->setHash(hash('md5', $uploadedFile->getClientOriginalName() . (string)time() . (string)rand()))
            ->setExtension($uploadedFile->getClientOriginalExtension());

        $em->persist($postfile);
        $em->flush();

        $uploadedFile->move($this->getUploadRootDirForUser($user), $postfile->getHash() . '.' . $postfile->getExtension());

        $responseMetadata = [
            'file_id' => $postfile->getId(),
        ];

        return new JsonResponse([
            'status' => 'ok',
            'metadata' => $responseMetadata,
        ]);
    }

    public function deleteAction(Request $request)
    {
        $id = $request->request->get('file_id');

        if (null === $id) {
            return new JsonResponse([
                'status' => 'badId'
            ]);
        }

        $file = $this->getDoctrine()->getRepository('NetworkStoreBundle:PostFile')->find($id);
        $user = $this->getUser();
        $path = $this->getUploadRootDirForUser($user) . $file->getHash() . '.' . $file->getExtension();
        unlink($path);

        $em = $this->getDoctrine()->getManager();
        $em->remove($file);
        $em->flush();

        return new JsonResponse([
            'status' => 'ok',
            'fileId' => $id
        ]);
    }

    public function downloadAction(Request $request)
    {
        $response = new Response();

        $file = $this->getDoctrine()->getRepository('NetworkStoreBundle:PostFile')->find($request->attributes->get('file_id'));

        if (null === $file || ($file->getHash() !== $request->query->get('h'))) {

            $response->setContent('file not found');

            return $response;
        }

        $user = $file->getPost()->getUser();

        $path = $this->getUploadRootDirForUser($user) . $file->getHash() . '.' . $file->getExtension();

        $content = file_get_contents($path);

        $response->headers->set('Content-Type', 'application/octect-stream');
        $response->headers->set('Content-Disposition', 'attachment;filename="' . $file->getName() . '"');
        $response->setContent($content);

        return $response;
    }
}
