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
        $customerRepository->method('buildCurrentPageQb')->willReturn([]);

        $manager = $this->getMockBuilder('Doctrine\ORM\EntityManager')->disableOriginalConstructor()->getMock();
        $manager->expects($this->any())
            ->method('getRepository')
            ->willReturn($customerRepository);

        $this->paginatorExtension = new PaginatorExtension($requestStack, $manager);

    }

    public function testGetPaginatorObjectByEntityClass()
    {
        $entityClass = Customer::class;
        $paginatorVar = 'test_param';

        $result = $this->paginatorExtension->getPaginatorObjectByEntityClass(
            $entityClass,
            $paginatorVar
        );

        $actualResultArrayKeys = array_keys($result);
        $expectedResultArrayKeys = ['max_pages', 'entity_collection', 'entities_count'];

        $this->assertEquals($actualResultArrayKeys, $expectedResultArrayKeys);
    }

    public function testGetPaginatorObjectByQb()
    {
        $entityClass = Customer::class;
        $paginatorVar = 'test_param';
        $queryBuilder = $this->queryBuilder;

        $result = $this->paginatorExtension->getPaginatorObjectByQb(
            $entityClass,
            $queryBuilder,
            $paginatorVar
        );

        $actualResultArrayKeys = array_keys($result);
        $expectedResultArrayKeys = ['max_pages', 'entity_collection', 'entities_count'];

        $this->assertEquals($actualResultArrayKeys, $expectedResultArrayKeys);
    }

    public function testGetFunctions()
    {
        $this->assertEquals(
            [
                new \Twig_SimpleFunction(
                    'paginator_object_by_qb',
                    [$this->paginatorExtension, 'getPaginatorObjectByQb']
                ),
                new \Twig_SimpleFunction(
                    'paginator_object_by_entity_class',
                    [$this->paginatorExtension, 'getPaginatorObjectByEntityClass']
                ),
            ],
            $this->paginatorExtension->getFunctions()
        );
    }
}
