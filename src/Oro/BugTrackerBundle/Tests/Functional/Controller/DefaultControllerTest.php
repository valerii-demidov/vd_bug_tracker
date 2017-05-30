<?php

namespace Oro\BugTrackerBundle\Tests\Controller;

use Oro\BugTrackerBundle\Entity\Customer;
use Symfony\Component\BrowserKit\Cookie;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Liip\FunctionalTestBundle\Test\WebTestCase;
use Oro\BugTrackerBundle\Tests\Functional\DataFixtures\LoadCustomerData;

class DefaultControllerTest extends WebTestCase
{
    protected $connection;
    private $client = null;

    public function setUp()
    {
        /*$this->connection = $this->getContainer()->get('doctrine.dbal.default_connection');
        /*$this->connection->beginTransaction();*/

        $this->loadFixtures([LoadCustomerData::class]);
        $this->client = static::createClient();
    }

    public function testIndex()
    {
        $this->logIn();
        $crawler = $this->client->request('GET', '/');
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
            array(),
            array(
                'PHP_AUTH_USER' => LoadCustomerData::TEST_USER_NAME,
                'PHP_AUTH_PW' => LoadCustomerData::TEST_USER_PASSWORD,
            )
        );
    }

    /**
     * In order to disable kernel shutdown
     * @see \Symfony\Bundle\FrameworkBundle\Test\KernelTestCase::tearDown
     */
    protected function tearDown()
    {
        parent::tearDown();
        /*$this->em->rollback();
        $this->em->close();*/
    }
}
