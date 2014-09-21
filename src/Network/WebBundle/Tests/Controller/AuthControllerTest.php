<?php

namespace Network\WebBundle\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class AuthControllerTest extends WebTestCase
{
    public function testFacebooklogin()
    {
        $client = static::createClient();

        $crawler = $client->request('GET', '/facebookLogin');
    }

}
