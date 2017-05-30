<?php

namespace Oro\BugTrackerBundle\Tests\Controller;

use Oro\BugTrackerBundle\Entity\Customer;
use Symfony\Component\BrowserKit\Cookie;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Liip\FunctionalTestBundle\Test\WebTestCase;
use Oro\BugTrackerBundle\Tests\Functional\DataFixtures\LoadProjectData;

class ProjectControllerTest extends WebTestCase
{
    protected $connection;
    private $client = null;

    public function setUp()
    {
        $this->loadFixtures([LoadProjectData::class]);
        $this->client = static::createClient();
    }

    public function testListAction()
    {
        $this->logIn();
        $crawler = $this->client->request('GET', $this->getUrl('oro_bugtracker_project_list'));
        $content = $this->client->getResponse()->getContent();

        $this->assertContains('Manage projects', $content);

        $this->assertContains(LoadProjectData::TEST_USER_PROJECT_CODE, $content);
        $this->assertContains(LoadProjectData::TEST_USER_PROJECT_SUMMARY, $content);
        $this->assertContains(LoadProjectData::TEST_USER_PROJECT_LABLEL, $content);

        $this->assertStatusCode(200, $this->client);
    }

    /**
     * @return int
     */
    public function testCreate()
    {
        $crawler = $this->client->request('GET', $this->getUrl('oro_bugtracker_project_create'));
        $result = $this->client->getResponse();
        $this->assertHtmlResponseStatusCodeEquals($result, 200);

        /** @var Customer $parent */
        $parent = $this->getReference('customer.level_1');
        /** @var CustomerGroup $group */
        $group = $this->getReference('customer_group.group1');
        /** @var AbstractEnumValue $internalRating */
        $internalRating = $this->getReference('internal_rating.1 of 5');
        /** @var CustomerTaxCode $customerTaxCode */
        $customerTaxCode = $this->getReference(LoadCustomerTaxCodes::REFERENCE_PREFIX.'.'.LoadCustomerTaxCodes::TAX_1);

        $this->assertCustomerSave($crawler, self::CUSTOMER_NAME, $parent, $group, $internalRating, $customerTaxCode);

        /** @var Customer $taxCustomer */
        $taxCustomer = $this->getContainer()->get('doctrine')
            ->getManagerForClass('OroCustomerBundle:Customer')
            ->getRepository('OroCustomerBundle:Customer')
            ->findOneBy(['name' => self::CUSTOMER_NAME]);
        $this->assertNotEmpty($taxCustomer);

        return $taxCustomer->getId();
    }

    private function logIn()
    {
        $this->client = static::createClient(
            array(),
            array(
                'PHP_AUTH_USER' => LoadProjectData::TEST_USER_NAME,
                'PHP_AUTH_PW' => LoadProjectData::TEST_USER_PASSWORD,
            )
        );
    }
}
