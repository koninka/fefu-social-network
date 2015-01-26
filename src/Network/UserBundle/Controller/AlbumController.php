<?php

namespace Network\UserBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Network\UserBundle\Form\Type\AlbumType;
use Network\UserBundle\Form\Type\PhotoType;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Network\StoreBundle\Entity\UserGallery;
use Application\Sonata\MediaBundle\Entity\GalleryHasMedia;
use Application\Sonata\MediaBundle\Entity\Gallery;

class AlbumController extends Controller
{
    use ProfileTrait;

    public function showAlbumAction($id, $albumId)
    {
        $user = $this->getDoctrine()->getRepository('NetworkStoreBundle:User')->find($id);
        if (empty($user)) return $this->redirect($this->generateUrl('mainpage'));

        $album = $this->getDoctrine()->getRepository('ApplicationSonataMediaBundle:Gallery')->find($albumId);
        if (empty($album)) {
            return $this->render('NetworkWebBundle:User:msg.html.twig', [
                'msg' => 'msg.show_nonexistent_album'
            ]);
        }

        $galleryHasMedia = $this->getDoctrine()->getRepository('ApplicationSonataMediaBundle:GalleryHasMedia');

        $isUserAlbum = $this->getDoctrine()->getRepository('NetworkStoreBundle:UserGallery')->isUserAlbum($id, $albumId);
        if (!$isUserAlbum) {
            return $this->render('NetworkWebBundle:User:msg.html.twig', [
                'msg' => 'msg.show_nonexistent_album'
            ]);
        }

        $isCurUserAlbum = $this->getDoctrine()->getRepository('NetworkStoreBundle:UserGallery')->isUserAlbum($this->getUser()->getId(), $albumId);

        return $this->render('NetworkUserBundle:Albums:album.html.twig', [
            'id' => $id,
            'is_cur_user' => $this->getUser()->getId() == $id,
            'is_user_album' => $isCurUserAlbum,
            'album' => $album,
            'photos' => $galleryHasMedia->findByGallery($albumId),
        ]);
    }

    public function editAlbumAction(Request $request)
    {
        $userId = $this->getUser()->getId();
        $albumId = $request->get('albumId');

        $userHasAlbum = $this->getDoctrine()->getRepository('NetworkStoreBundle:UserGallery')->findAlbumForUser($userId, $albumId);
        if (empty($userHasAlbum)) {
            return $this->render('NetworkWebBundle:User:msg.html.twig', [
                'msg' => 'msg.edit_inaccessible_album'
            ]);
        }

        $album = $userHasAlbum->getGallery();

        $form = $this->createForm(new AlbumType());
        $form->setData($album);
        $form->handleRequest($request);

        if ($form->isValid()) {
            $data = $form->getData();

            $album->setName($data->getName())
                  ->setContext('default')
                  ->setDescription($data->getDescription())
                  ->setDefaultFormat('default_small')
                  ->setEnabled(true);

            $em = $this->getDoctrine()->getManager();
            $em->persist($album);
            $em->flush();

            $url = $this->generateUrl('user_show_albums', ['id' => $userId]);

            return new RedirectResponse($url);
        }

        return $this->render('NetworkUserBundle:Albums:edit_album.html.twig', [
            'form' => $form->createView(),
            'album' => $album,
        ]);
    }

    public function deleteAlbumAction($albumId)
    {
        $user = $this->getUser();
        $userHasAlbum = $this->getDoctrine()->getRepository('NetworkStoreBundle:UserGallery')->findAlbumForUser($user->getId(), $albumId);
        $msg = 'msg.album_not_found';

        if (empty($userHasAlbum)) {
            $msg = 'msg.delete_inaccessible_album';
        } else {
            $userAlbum = $userHasAlbum->getGallery();
            $em = $this->getDoctrine()->getManager();
            $em->remove($userHasAlbum);

            $galleryHasMedia = $this->getDoctrine()->getRepository('ApplicationSonataMediaBundle:GalleryHasMedia');
            foreach ($galleryHasMedia->findByGallery($albumId) as $n) {
                $em->remove($n->getMedia());
                $em->remove($n);
            }

            $em->remove($userAlbum); 
            $em->flush();
            $user->removeAlbum($userHasAlbum);
            $msg = 'msg.user_album_deleted';
        }

        return $this->render('NetworkWebBundle:User:msg.html.twig', [
            'msg' => $msg
        ]);
    }

    public function addAlbumAction(Request $request)
    {
        $user = $this->getUser();

        $form = $this->createForm(new AlbumType());
        $form->handleRequest($request);

        if ($form->isValid()) {
            $newAlbum = new Gallery();
            $data = $form->getData();
            $newAlbum->setName($data->getName())
                     ->setContext('default')
                     ->setDefaultFormat('default_small')
                     ->setEnabled(true);

            $userAlbum = new UserGallery();
            $userAlbum->setOwner($user)
                      ->setGallery($newAlbum);

            $em = $this->getDoctrine()->getManager();
            $em->persist($newAlbum);
            $em->persist($userAlbum);
            $em->flush();

            $user->addAlbum($userAlbum);

            $userManager = $this->get('fos_user.user_manager');
            $userManager->updateUser($user);

            $url = $this->generateUrl('user_show_albums', ['id' => $user->getId()]);
            $response = new RedirectResponse($url);

            return $response;
        }

        return $this->render('NetworkUserBundle:Albums:add_album.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    public function addAlbumPhotoAction(Request $request)
    {
        $userId = $this->getUser()->getId();
        $albumId = $request->get('albumId');

        $userHasAlbum = $this->getDoctrine()->getRepository('NetworkStoreBundle:UserGallery')->findAlbumForUser($userId, $albumId);
        if (empty($userHasAlbum)) {
            return $this->render('NetworkWebBundle:User:msg.html.twig', [
                'msg' => 'msg.add_photo_to_inaccessible_album'
            ]);
        }

        $album = $userHasAlbum->getGallery();
        if (empty($album)) {
            return $this->render('NetworkWebBundle:User:msg.html.twig', [
                'msg' => 'msg.add_photo_to_nonexistent_album'
            ]);
        }

        $form = $this->createForm(new PhotoType());
        $form->handleRequest($request);

        if ($form->isValid()) {
            $data = $form->getData();
            $data['media']->setDescription($data['description']);

            $em = $this->getDoctrine()->getManager();
            $em->persist($data['media']);

            $ghm = new GalleryHasMedia();
            $ghm->setGallery($album)
                ->setMedia($data['media']);

            $em->persist($ghm);
            $em->flush();

            $album->addGalleryHasMedia($ghm);

            $url = $this->generateUrl('user_show_album', ['id' => $userId, 'albumId' => $albumId]);
            $response = new RedirectResponse($url);

            return $response;
        }

        return $this->render('NetworkUserBundle:Albums:add_album_photo.html.twig', [
            'form' => $form->createView(),
            'album_id' => $albumId,
        ]);
    }

    public function deleteAlbumPhotoAction($albumId, $photoId)
    {
        $user = $this->getUser();
        $userId = $user->getId();
        $userHasAlbum = $this->getDoctrine()->getRepository('NetworkStoreBundle:UserGallery')->findAlbumForUser($userId, $albumId);
        $msg = 'msg.photo_not_found';

        if (empty($userHasAlbum)) {
            $msg = 'msg.delete_inaccessible_photo';
        } else {
            $gallery = $userHasAlbum->getGallery();
            $galleryHasMedia = $gallery->getGalleryHasMedia($photoId);
            if (!empty($galleryHasMedia)) {
                $photo = $galleryHasMedia->getMedia();
                $gallery->removeGalleryHasMedia($galleryHasMedia);
                $em = $this->getDoctrine()->getManager();
                $em->remove($galleryHasMedia);
                $em->remove($photo);
                $em->flush();
                $msg = 'msg.user_album_photo_deleted';
            }
        }

        return $this->render('NetworkWebBundle:User:msg.html.twig', ['msg' => $msg]);
    }

}
