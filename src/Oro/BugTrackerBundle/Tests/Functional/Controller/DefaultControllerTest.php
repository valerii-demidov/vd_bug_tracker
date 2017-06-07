<?php

namespace Oro\BugTrackerBundle\Tests\Controller;

use Liip\FunctionalTestBundle\Test\WebTestCase;
use Oro\BugTrackerBundle\Tests\Functional\DataFixtures\LoadCustomerData;

class DefaultControllerTest extends WebTestCase
{
    protected $connection;
    private $client;

    public function setUp()
    {
        $this->loadFixtures([LoadCustomerData::class]);
        $this->client = static::createClient();
    }

    public function testIndex()
    {
        $this->logIn();
        $this->client->request('GET', '/');
        $content = $this->client->getResponse()->getContent();

        $this->assertContains('Home page', $content);

        $this->assertContains(LoadCustomerData::TEST_USER_EMAIL, $content);
        $this->assertContains(LoadCustomerData::TEST_USER_ISSUE_CODE, $content);
        $this->assertContains('Activities', $content);

        $this->assertStatusCode(200, $this->client);
    }

    private function logIn()
    {
        $this->client = static::createClient(
            [],
            [
                'PHP_AUTH_USER' => LoadCustomerData::TEST_USER_NAME,
                'PHP_AUTH_PW' => LoadCustomerData::TEST_USER_PASSWORD,
            ]
        );
    }
}
