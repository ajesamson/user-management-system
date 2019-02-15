<?php

namespace App\Tests;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;

class GroupControllerTest extends WebTestCase
{
    private static $groupEndpoint = '/groups/';

    private static $loginCheck = '/api/login_check';

    /** @var \Faker\Factory */
    private $faker;

    private $groupName;

    private $authenticatedClient;

    protected function setUp()
    {
        parent::setUp();
        $this->faker = \Faker\Factory::create();
        $this->groupName = $this->faker->word . $this->faker->randomNumber();
        $this->authenticatedClient = $this->createAuthenticatedClient();
    }

    public function testGroupEndpointIsSecure()
    {
        $client = static::createClient();
        $client->request('GET', self::$groupEndpoint);

        $this->assertSame(Response::HTTP_UNAUTHORIZED, $client->getResponse()->getStatusCode());
    }

    /*public function testGroupIndexEndpoint()
    {
        $this->authenticatedClient->request('GET', self::$groupEndpoint);

        var_dump($this->authenticatedClient->getResponse()->getContent());
        $this->assertSame(
            Response::HTTP_OK,
            $this->authenticatedClient->getResponse()->getStatusCode()
        );
    }*/

    public function testGroupAddEndpoint()
    {
        $client = $this->createGroup($this->groupName);

        $this->assertSame(Response::HTTP_CREATED, $client->getResponse()->getStatusCode());
        $this->assertContains($this->groupName, $client->getResponse()->getContent());
    }

    public function testGroupAddEndpointWithEmptyName()
    {
        $this->authenticatedClient->request(
            'POST',
            self::$groupEndpoint . 'add',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            '{"name":""}'
        );

        $this->assertSame(Response::HTTP_BAD_REQUEST, $this->authenticatedClient->getResponse()->getStatusCode());
    }

    public function testGroupDeleteEndpoint()
    {
        $client = $this->createGroup($this->groupName . \Faker\Factory::create()->randomNumber());

        $data = json_decode($client->getResponse()->getContent(), true);
        $groupId = $data['data']['id'];
        $client->request('DELETE', self::$groupEndpoint . $groupId);

        $this->assertSame(Response::HTTP_NO_CONTENT, $client->getResponse()->getStatusCode());
    }

    public function testGroupDeleteEndpointWithInvalidGroupId()
    {
        $groupId = '12345';
        $this->authenticatedClient->request('DELETE', self::$groupEndpoint . $groupId);

        $this->assertSame(
            Response::HTTP_BAD_REQUEST,
            $this->authenticatedClient->getResponse()->getStatusCode()
        );
    }

    protected function createGroup($groupName)
    {
        $this->authenticatedClient->request(
            'POST',
            self::$groupEndpoint . 'add',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            '{"name":"' . $groupName . '"}'
        );

        return $this->authenticatedClient;
    }

    protected function createAuthenticatedClient()
    {
        $client = static::createClient();
        $client->request(
            'POST',
            self::$loginCheck,
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            '{"username":"admin","password":"admin"}'
        );

        $data = json_decode($client->getResponse()->getContent(), true);

        $client = static::createClient();
        $client->setServerParameter('HTTP_Authorization', sprintf('Bearer %s', $data['token']));

        return $client;
    }
}
