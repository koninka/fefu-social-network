<?php

namespace Network\OAuthBundle\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class ResumeRegistrationControllerTest extends WebTestCase
{
    public function testResume()
    {
        $client = static::createClient();

        $crawler = $client->request('GET', '/resume');
    }

}
