<?php

namespace Network\UserBundle\Controller;

use FOS\UserBundle\FOSUserEvents;
use FOS\UserBundle\Event\FormEvent;
use FOS\UserBundle\Event\FilterUserResponseEvent;
use FOS\UserBundle\Event\GetResponseUserEvent;
use FOS\UserBundle\Model\UserInterface;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

use Network\UserBundle\Form\Type\ChangeEmailType;


class ChangeEmailController extends Controller
{
    use ProfileTrait;
    /**
     * Change user email
     */
    public function changeEmailAction(Request $request)
    {
        $user = $this->getUser();
        if (!is_object($user) || !$user instanceof UserInterface) {
            throw new AccessDeniedException('This user does not have access to this section.');
        }

        /** @var $dispatcher \Symfony\Component\EventDispatcher\EventDispatcherInterface */
        $dispatcher = $this->get('event_dispatcher');

        $event = new GetResponseUserEvent($user, $request);
        $dispatcher->dispatch(FOSUserEvents::PROFILE_EDIT_INITIALIZE, $event);

        if (null !== $event->getResponse()) {
            return $event->getResponse();
        }

        $form = $this->createForm(new ChangeEmailType());
        $form->setData($user);
        $form->get('email')->setData(' ');
        $form->get('plainPassword')->setData(' ');

        $form->handleRequest($request);

        if ($form->isValid()) {
            /** @var $userManager \FOS\UserBundle\Model\UserManagerInterface */
            $userManager = $this->get('fos_user.user_manager');
            if (!$this->validUser($user->getUsername(), $user->getPlainPassword()))
            {
                $form->get('plainPassword')->addError(new FormError('Invalid password'));

                return $this->render('NetworkUserBundle:ChangeEmail:changeEmail.html.twig', [
                    'form' => $form->createView(),
                ]);
            }
            $dispatcher = $this->get('event_dispatcher');
            $event = new FormEvent($form, $request);
            $dispatcher->dispatch(FOSUserEvents::PROFILE_EDIT_SUCCESS, $event);

            $userManager->updateUser($user);

            if (null === $response = $event->getResponse()) {
                $url = $this->generateUrl('fos_user_profile_show');
                $response = new RedirectResponse($url);
            }

            $dispatcher->dispatch(FOSUserEvents::PROFILE_EDIT_COMPLETED, new FilterUserResponseEvent($user, $request, $response));

            return $response;
        }

        return $this->render('NetworkUserBundle:ChangeEmail:changeEmail.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    public function validUser($username, $password)
    {
        $user_manager = $this->get('fos_user.user_manager');
        $factory = $this->get('security.encoder_factory');

        $user = $user_manager->loadUserByUsername($username);

        $encoder = $factory->getEncoder($user);

        return ($encoder->isPasswordValid($user->getPassword(), $password, $user->getSalt())) ? true : false;
    }

}
