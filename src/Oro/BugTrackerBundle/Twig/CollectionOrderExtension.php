<?php

namespace Oro\BugTrackerBundle\Twig;

use Doctrine\Common\Collections\Criteria;
use Doctrine\Common\Collections\Collection;

class CollectionOrderExtension extends \Twig_Extension
{
    public function getFunctions()
    {
        return [
            new \Twig_SimpleFunction('collection_order', [$this, 'getOrderedCollection']),
        ];
    }

    /**
     * @param Collection $collection
     * @param $fieldName
     * @param string $orderType
     * @return mixed
     */
    public function getOrderedCollection(Collection $collection, $fieldName, $orderType = Criteria::DESC)
    {
        $criteria = new Criteria();
        $criteria->orderBy([$fieldName => $orderType]);
        $comments = $collection->matching($criteria);

        return $comments;
    }
}
