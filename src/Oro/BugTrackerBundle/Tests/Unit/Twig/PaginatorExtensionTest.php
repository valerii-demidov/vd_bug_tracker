<?php

namespace Oro\BugTrackerBundle\Tests\Unit\Twig;

use PHPUnit\Framework\TestCase;
use Oro\BugTrackerBundle\Twig\PaginatorExtension;
use Symfony\Component\HttpFoundation\RequestStack;
use Doctrine\ORM\QueryBuilder;
use Symfony\Component\HttpFoundation\Request;
use Oro\BugTrackerBundle\Entity\Customer;
use Oro\BugTrackerBundle\Repository\CustomerRepository;


class PaginatorExtensionTest extends TestCase
{

    /** @var QueryBuilder */
    protected $queryBuilder;

    public function setUp()
    {
        $requestStack = new RequestStack();
        $request = Request::create('/foo');
        $requestStack->push($request);

        $customerRepository = $this->getMockBuilder(CustomerRepository::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->queryBuilder = $this->getMockBuilder(QueryBuilder::class)
            ->disableOriginalConstructor()
            ->getMock();

        $customerRepository->method('createQueryBuilder')->willReturn($this->queryBuilder);
        $customerRepository->method('getCurrentPageByQb')->willReturn([]);
        $customerRepository->method('getQbByCustomCondition')->willReturn($this->queryBuilder);

        $manager = $this->getMockBuilder('Doctrine\ORM\EntityManager')->disableOriginalConstructor()->getMock();
        $manager->expects($this->any())
            ->method('getRepository')
            ->willReturn($customerRepository);

        $this->paginatorExtension = new PaginatorExtension($requestStack, $manager);

    }

    public function testGetFunctions()
    {
        $functions = $this->paginatorExtension->getFunctions();

        $this->assertInternalType('array', $functions);
        $this->assertArrayHasKey(0, $functions);
        $this->assertInstanceOf('\Twig_SimpleFunction', $functions[0]);

        $function = $functions[0];
        $this->assertInternalType('string', $function->getName());
        $this->assertEquals($function->getName(),'paginator_object_by_custom_condition');
        $this->assertInternalType('callable', $function->getCallable());

        $function = $functions[1];
        $this->assertInternalType('string', $function->getName());
        $this->assertEquals($function->getName(),'paginator_object_by_entity_class');
        $this->assertInternalType('callable', $function->getCallable());
    }


    public function testGetPaginatorObjectByEntityClass()
    {
        $entityClass = Customer::class;
        $paginatorVar = 'test_param';

        $result = $this->paginatorExtension->getPaginatorObjectByEntityClass(
            $entityClass,
            $paginatorVar
        );

        $this->assertInternalType('array', $result);
        $this->assertArrayHasKey('max_pages', $result);
        $this->assertArrayHasKey('entity_collection', $result);
        $this->assertArrayHasKey('entities_count', $result);
    }

   public function testGetPaginatorCustomCondition()
    {
        $entityClass = Customer::class;
        $methodName = 'customer_issues';
        $paginatorVar = 'issue_p';
        $methodAttributes = [new Customer];


        $result = $this->paginatorExtension->getPaginatorCustomCondition(
            $entityClass,
            $methodName,
            $paginatorVar,
            $methodAttributes
        );

        $this->assertInternalType('array', $result);
        $this->assertArrayHasKey('max_pages', $result);
        $this->assertArrayHasKey('entity_collection', $result);
        $this->assertArrayHasKey('entities_count', $result);
    }
}