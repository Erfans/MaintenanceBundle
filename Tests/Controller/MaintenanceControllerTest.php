<?php

namespace Erfans\MaintenanceBundle\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class MaintenanceControllerTest extends WebTestCase
{
    public function testMaintenance()
    {
        $client = static::createClient();

        $crawler = $client->request('GET', '/maintenance');

        $this->assertTest($client->getResponse()->isSuccessful());
    }

}
