<?php

namespace App\Tests;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;

class UserControllerTest extends WebTestCase
{
    private static $userEndpoint = '/users/';

    private static $groupEndpoint = '/groups/';

    private static $loginCheck = '/api/login_check';

    /** @var \Faker\Factory */
    private $faker;

    private $authenticatedClient;

    private $username;

    private $userForGroup;

    private $groupName;

    protected function setUp()
    {
        parent::setUp();
        $this->faker = \Faker\Factory::create();
        $this->username = $this->faker->firstName;
        $this->userForGroup = $this->faker->lastName;
        $this->groupName = $this->faker->word;
        $this->authenticatedClient = $this->createAuthenticatedClient();
    }

    public function testUserEndpointIsSecure()
    {
        $client = static::createClient();
        $client->request('GET', self::$userEndpoint);

        $this->assertSame(Response::HTTP_UNAUTHORIZED, $client->getResponse()->getStatusCode());
    }

    public function testUserIndexEndpoint()
    {
        $this->authenticatedClient->request('GET', self::$userEndpoint);

        $this->assertSame(
            Response::HTTP_OK,
            $this->authenticatedClient->getResponse()->getStatusCode()
        );
    }

    public function testUsersAddEnpointWithValidName()
    {
        $client = $this->createUser($this->username);

        $this->assertSame(Response::HTTP_CREATED, $client->getResponse()->getStatusCode());
        $this->assertContains($this->username, $client->getResponse()->getContent());
    }

    public function testUsersAddEndpointWithInvalidName()
    {
        $this->authenticatedClient->request(
            'POST',
            self::$userEndpoint . 'add',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            '{"name":""}'
        );

        $this->assertSame(
            Response::HTTP_BAD_REQUEST,
            $this->authenticatedClient->getResponse()->getStatusCode()
        );
    }

    public function testUsersDeleteEndpoint()
    {
        $username = $this->faker->firstName;
        $client = $this->createUser($username);

        $data = json_decode($client->getResponse()->getContent(), true);
        $userId = $data['data']['id'];
        $client->request('DELETE', self::$userEndpoint . $userId);

        $this->assertSame(Response::HTTP_NO_CONTENT, $client->getResponse()->getStatusCode());
    }

    public function testUsersDeleteEndpoinWithInvalidId()
    {
        $this->authenticatedClient->request('DELETE', self::$userEndpoint . '100000');

        $this->assertSame(
            Response::HTTP_BAD_REQUEST,
            $this->authenticatedClient->getResponse()->getStatusCode()
        );
    }

    public function testUserAddGroupEndpoint()
    {
        $group = $this->createGroup($this->groupName . \Faker\Factory::create()->randomNumber());
        $client = $this->createUser($this->userForGroup . \Faker\Factory::create()->randomNumber());

        $data = json_decode($client->getResponse()->getContent(), true);
        $userId = $data['data']['id'];

        $client->request(
            'POST',
            self::$userEndpoint . $userId . '/add-group',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            '{"name":"' . $group['name'] . '"}'
        );

        $this->assertSame(Response::HTTP_CREATED, $client->getResponse()->getStatusCode());
    }

    public function testUserAddGroupEndpointWithInvalidUser()
    {
        $this->authenticatedClient->request(
            'POST',
            self::$userEndpoint  . '1000000/add-group',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            '{"name":"' . $this->groupName . '"}'
        );

        $this->assertSame(
            Response::HTTP_BAD_REQUEST,
            $this->authenticatedClient->getResponse()->getStatusCode()
        );
    }

    public function testUserAddGroupEndpointWithInvalidGroup()
    {
        $username = $this->userForGroup . \Faker\Factory::create()->randomNumber();
        $groupName = '10000';

        $client = $this->createUser($username);

        $data = json_decode($client->getResponse()->getContent(), true);
        $userId = $data['data']['id'];

        $client->request(
            'POST',
            self::$userEndpoint . $userId . '/add-group',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            '{"name":"' . $groupName . '"}'
        );

        $this->assertSame(Response::HTTP_BAD_REQUEST, $client->getResponse()->getStatusCode());
    }

    public function testUserDeleteGroupEndpoint()
    {
        $username = $this->userForGroup . \Faker\Factory::create()->randomNumber();
        $groupName = $this->groupName . \Faker\Factory::create()->randomNumber();

        $group = $this->createGroup($groupName);
        $client = $this->createUser($username);

        $data = json_decode($client->getResponse()->getContent(), true);
        $userId = $data['data']['id'];

        $client->request(
            'DELETE',
            self::$userEndpoint . $userId . '/delete-group',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            '{"name":"' . $group['name'] . '"}'
        );

        $this->assertSame(Response::HTTP_NO_CONTENT, $client->getResponse()->getStatusCode());
    }

    public function testUserDeleteGroupEndpointWithInvalidGroup()
    {
        $username = $this->username . \Faker\Factory::create()->randomNumber();
        $groupName = '10000000';

        $client = $this->createUser($username);

        $data = json_decode($client->getResponse()->getContent(), true);
        $userId = $data['data']['id'];

        $client->request(
            'DELETE',
            self::$userEndpoint . $userId . '/delete-group',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            '{"name":"' . $groupName . '"}'
        );

        $this->assertSame(Response::HTTP_BAD_REQUEST, $client->getResponse()->getStatusCode());
    }

    public function testUserDeleteGroupEndpointWithInvalidUser()
    {
        $groupName = $this->groupName . \Faker\Factory::create()->randomNumber();
        $group = $this->createGroup($groupName);

        $this->authenticatedClient->request(
            'DELETE',
            self::$userEndpoint . '1000000/delete-group',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            '{"name":"' . $group['name'] . '"}'
        );

        $this->assertSame(
            Response::HTTP_BAD_REQUEST,
            $this->authenticatedClient->getResponse()->getStatusCode()
        );
    }

    protected function createUser($username)
    {
        $this->authenticatedClient->request(
            'POST',
            self::$userEndpoint . 'add',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            '{"name":"' . $username . '"}'
        );

        return $this->authenticatedClient;
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

        $data = json_decode($this->authenticatedClient->getResponse()->getContent(), true);

        return $data['data'];
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
