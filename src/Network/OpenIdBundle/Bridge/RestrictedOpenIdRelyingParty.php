<?php
/**
 * Created by PhpStorm.
 * User: user
 * Date: 01.03.2015
 * Time: 15:15
 */

namespace Network\OpenIdBundle\Bridge;

use Symfony\Component\DependencyInjection\ContainerInterface as Container;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RedirectResponse;

use Fp\OpenIdBundle\Bridge\RelyingParty\LightOpenIdRelyingParty;
use Fp\OpenIdBundle\RelyingParty\IdentityProviderResponse;
use Fp\OpenIdBundle\RelyingParty\Exception\OpenIdAuthenticationCanceledException;
use Fp\OpenIdBundle\RelyingParty\Exception\OpenIdAuthenticationValidationFailedException;

class RestrictedOpenIdRelyingParty extends LightOpenIdRelyingParty
{
    private $container;

    public function __construct(Container $container) {
        $this->container = $container;
    }

    protected function guessIdentifier(Request $request)
    {
        foreach ($this->container->getParameter('valid_openid_providers') as $provider) {
            $providers[] = $provider['url'];
        }

        if(in_array($request->get('openid_identifier'), $providers)) {
            return $request->get('openid_identifier');
        } else {
            throw new OpenIdAuthenticationValidationFailedException("Invalid OpenID provider used", 1);
        }
    }
}
