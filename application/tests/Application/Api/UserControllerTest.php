<?php

namespace App\Tests\Application\Api;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class UserControllerTest extends WebTestCase
{
    public function testFindUsersByIsActive()
    {
        $client = static::createClient();

        $client->request('GET', '/users?is_active=1');

        $this->assertEquals(200, $client->getResponse()->getStatusCode());
        $this->assertJson($client->getResponse()->getContent());
    }

    public function testFindUsersByIsMember()
    {
        $client = static::createClient();

        $client->request('GET', '/users?is_member=1');

        $this->assertEquals(200, $client->getResponse()->getStatusCode());
        $this->assertJson($client->getResponse()->getContent());
    }

    public function testFindUsersByLastLoginAt()
    {
        $client = static::createClient();

        $client->request('GET', '/users?last_login_at=2020-12-12 to 2022-12-12');

        $this->assertEquals(200, $client->getResponse()->getStatusCode());
        $this->assertJson($client->getResponse()->getContent());
    }

    public function testFindUsersByUserType()
    {
        $client = static::createClient();

        $client->request('GET', '/users?user_type=1');

        $this->assertEquals(200, $client->getResponse()->getStatusCode());
        $this->assertJson($client->getResponse()->getContent());
    }

    public function testPagination()
    {
        $client = static::createClient();

        $client->request('GET', '/users', ['page' => 2, 'limit' => 10]);

        $this->assertSame(200, $client->getResponse()->getStatusCode());

        $responseData = json_decode($client->getResponse()->getContent(), true);
        $this->assertCount(10, $responseData['data']);
        $this->assertCount(2, $responseData['meta']['current_page']);
    }

    public function testPaginationWithInvalidPage()
    {
        $client = static::createClient();

        $client->request('GET', '/users', ['page' => 0, 'limit' => 10]);

        $this->assertSame(400, $client->getResponse()->getStatusCode());
    }
}
