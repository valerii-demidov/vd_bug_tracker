<?php

namespace Oro\BugTrackerBundle\Tests\Unit\Twig;

use Oro\BugTrackerBundle\Twig\CollectionOrderExtension;
use Oro\BugTrackerBundle\Entity\Issue;
use PHPUnit\Framework\TestCase;
use Doctrine\Common\Collections\Criteria;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Common\Collections\ArrayCollection;

class CollectionOrderExtensionTest extends TestCase
{
    const COLLECTION_COUNT = 5;

    /** @var  CollectionOrderExtension */
    protected $collectionOrderExtension;

    /** @var  EntityManagerInterface */
    protected $em;

    public function setUp()
    {
        $this->em = $this
            ->getMockBuilder(EntityManagerInterface::class)
            ->getMock();
        $this->collectionOrderExtension = new CollectionOrderExtension();
    }

    public function testGetFunctions()
    {
        $functions = $this->collectionOrderExtension->getFunctions();

        $this->assertInternalType('array', $functions);
        $this->assertArrayHasKey(0, $functions);
        $this->assertInstanceOf('\Twig_SimpleFunction', $functions[0]);
        $function = $functions[0];
        $this->assertInternalType('string', $function->getName());
        $this->assertEquals($function->getName(), 'collection_order');
        $this->assertInternalType('callable', $function->getCallable());
    }

    public function testGetOrderedCollection()
    {
        $criteria = new Criteria();
        $fieldName = 'code';
        $orderType = Criteria::DESC;
        $criteria->orderBy([$fieldName => $orderType]);

        $issueExpectedArrayCollection = $this->buildIssueCollection();
        $issueActualArrayCollection = clone $issueExpectedArrayCollection;
        $orderedExpectedCollection = $issueExpectedArrayCollection->matching($criteria);

        $orderedActualCollection = $this->collectionOrderExtension->getOrderedCollection(
            $issueActualArrayCollection,
            $fieldName,
            $orderType
        );

        $this->assertSame($orderedExpectedCollection->toArray(), $orderedActualCollection->toArray());

        $issueExpectedArrayCollection = $this->buildIssueCollection();
        $issueActualArrayCollection = clone $issueExpectedArrayCollection;

        $orderedActualCollection = $this->collectionOrderExtension->getOrderedCollection(
            $issueActualArrayCollection,
            $fieldName,
            $orderType
        );

        $this->assertNotSame($issueExpectedArrayCollection->toArray(), $orderedActualCollection->toArray());
    }

    /**
     * @return ArrayCollection
     */
    private function buildIssueCollection()
    {
        $issueCollection = [];
        for ($i = 0; $i < self::COLLECTION_COUNT; $i++) {
            $issueCollection[] = $this->buildIssueEntity();
        }

        return new ArrayCollection($issueCollection);
    }

    /**
     * @return ArrayCollection
     */
    private function buildIssueEntity()
    {
        $issue = new Issue();
        $issue->setCode(random_int(0, 100));

        return $issue;
    }
}
