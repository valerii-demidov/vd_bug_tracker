<?php

namespace Oro\BugTrackerBundle\Twig;

use Doctrine\Common\Collections\Criteria;
use Oro\BugTrackerBundle\Entity\Issue;

class CollectionOrderExtension extends \Twig_Extension
{
    public function getFunctions()
    {
        return [
            new \Twig_SimpleFunction('collection_order', [$this, 'getOrderedCollection']),
        ];
    }

    /**
     * @param $collection
     * @param $fieldName
     * @param string $orderType
     * @return array
     */
    public function getOrderedCollection($collection, $fieldName, $orderType = Criteria::DESC)
    {
        $criteria = new Criteria();
        $criteria->orderBy([$fieldName => $orderType]);
        $comments = $collection->matching($criteria);

        return $comments;
    }
}
