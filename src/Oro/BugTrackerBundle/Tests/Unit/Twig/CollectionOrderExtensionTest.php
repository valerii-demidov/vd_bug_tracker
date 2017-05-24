<?php

namespace Oro\BugTrackerBundle\Tests\Unit\Twig;

use Oro\BugTrackerBundle\Twig\CollectionOrderExtension;
use Oro\BugTrackerBundle\Entity\Issue;
use PHPUnit\Framework\TestCase;
use Doctrine\Common\Collections\Criteria;
use Doctrine\ORM\PersistentCollection;
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
        $this->assertEquals(
            [
                new \Twig_SimpleFunction(
                    'collection_order',
                    [$this->collectionOrderExtension, 'getOrderedCollection']
                ),
            ],
            $this->collectionOrderExtension->getFunctions()
        );
    }


    public function testGetOrderedCollection()
    {
        $issueArrayCollection = $this->buildIssueCollection();
        $expectedCollection = new PersistentCollection($this->em, Issue::class, $issueArrayCollection);
        $criteria = new Criteria();
        $fieldName = 'code';
        $orderType = Criteria::DESC;
        $criteria->orderBy([$fieldName => $orderType]);
        $orderedExpectedCollection = $expectedCollection->matching($criteria);

        $acualCollection = new PersistentCollection($this->em, Issue::class, $issueArrayCollection);
        $orderedActualCollection = $this->collectionOrderExtension->getOrderedCollection(
            $acualCollection,
            $fieldName,
            $orderType
        );

        $this->assertEquals($orderedExpectedCollection->toArray(), $orderedActualCollection->toArray());
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
        $issue->setCode(random_int(0,100));

        return $issue;
    }
}
