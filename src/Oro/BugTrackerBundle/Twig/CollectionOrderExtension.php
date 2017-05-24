<?php

namespace Oro\BugTrackerBundle\Twig;

use Doctrine\Common\Collections\Criteria;
use Doctrine\ORM\PersistentCollection;

class CollectionOrderExtension extends \Twig_Extension
{
    public function getFunctions()
    {
        return [
            new \Twig_SimpleFunction('collection_order', [$this, 'getOrderedCollection']),
        ];
    }

    /**
     * @param PersistentCollection $collection
     * @param string $fieldName
     * @param string $orderType
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getOrderedCollection(PersistentCollection $collection, $fieldName, $orderType = Criteria::DESC)
    {
        $criteria = new Criteria();
        $criteria->orderBy([$fieldName => $orderType]);
        $comments = $collection->matching($criteria);

        return $comments;
    }
}
