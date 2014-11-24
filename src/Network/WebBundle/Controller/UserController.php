<?php

namespace Network\WebBundle\Controller;

use Network\StoreBundle\Form\Type\JobsCollectionType;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Network\StoreBundle\Form\Type\UserType;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\Request;

class UserController extends Controller
{

    public function jobsAction($id, Request $request) {
        $user = $this->get('security.context')->getToken()->getUser();
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

}
