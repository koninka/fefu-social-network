<?php

namespace Network\OAuthBundle\Controller;

use Network\UserBundle\Form\Type\RegistrationType;
use Proxies\__CG__\Network\StoreBundle\Entity\User;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\Config\Definition\Exception\Exception;
use Symfony\Component\DependencyInjection\ContainerAware;
use Symfony\Component\HttpFoundation\File\Exception\AccessDeniedException;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Security\Core\Exception\AccountStatusException;

class ResumeRegistrationController extends ContainerAware
{
    /**
     * @Route("/resume")
     * @Template()
     * @param Request $request
     * @return array
     */
    public function resumeAction(Request $request)
    {
        $serial_token = $request->getSession()->get('_security_secured_area');
        $token = unserialize($serial_token);
        $userInformation = $this->getResourceOwnerByName($token->getResourceOwnerName());
        $form = $this->container
                     ->get('hwi_oauth.registration.form.factory')
                     ->createForm(new RegistrationType(User::class, $request->getSession()));

        $key = time();

        return $this->container->get('templating')
            ->renderResponse('NetworkOAuthBundle:ResumeRegistration:resume.html.' . $this->getTemplatingEngine(), array(
            'key' => $key,
            'form' => $form->createView(),
            'userInformation' => $userInformation,
        ));
    }

    public function addMissingDataAction(Request $request, $key)
    {
        //TODO add email confirmation
        $user = $this->container->get('security.context')->getToken()->getUser();
        $userManager = $this->container->get('fos_user.user_manager');
        $form = $_POST['fos_user_registration_form'];
        if ($form != null && !empty($form)) {
            $user->setGender($form['gender']);
            $user->setFirstName($form['firstName']);
            $user->setLastName($form['lastName']);
            $user->setEmail($form['email']);
            $userManager->updateUser($user);
        }
        try {
            $this->container->get('hwi_oauth.user_checker')->checkPostAuth($user);
        } catch (AccountStatusException $e) {
            // Don't authenticate locked, disabled or expired users
            return;
        }
        $url = $this->container->get('router')->generate('fos_user_profile_show');
        $response = new RedirectResponse($url);
        return $response;
    }

    protected function getResourceOwnerByName($name)
    {
        $ownerMap = $this->container->get('hwi_oauth.resource_ownermap.' . $this->container->getParameter('hwi_oauth.firewall_name'));

        if (null === $resourceOwner = $ownerMap->getResourceOwnerByName($name)) {
            throw new \RuntimeException(sprintf("No resource owner with name '%s'.", $name));
        }

        return $resourceOwner;
    }

    protected function getTemplatingEngine()
    {
        return $this->container->getParameter('hwi_oauth.templating.engine');
    }

}
