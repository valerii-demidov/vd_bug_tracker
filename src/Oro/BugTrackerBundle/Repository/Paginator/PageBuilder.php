<?php

namespace Oro\BugTrackerBundle\Repository\Paginator;

use Doctrine\ORM\QueryBuilder;

trait PageBuilder
{
    /**
     * @param QueryBuilder $queryBuilder
     * @param $currentPage
     * @param $pageSize
     *
     * @return array
     */
    public function getCurrentPageByQb(QueryBuilder $queryBuilder, $currentPage, $pageSize)
    {
        $result['max_pages'] = 0;
        $result['entity_collection'] = [];
        $result['entities_count'] = 0;

        $entityAlias = current($queryBuilder->getRootAliases());
        if ($entityAlias) {
            $cloneQb = clone $queryBuilder;

            $result['entity_collection'] = $queryBuilder
                ->getQuery()
                ->setFirstResult($pageSize * ($currentPage - 1))// Offset
                ->setMaxResults($pageSize)
                ->getResult();

            // get collection qty
            $cloneQueryBuilder = $cloneQb;
            $cloneQueryBuilder->select("count($entityAlias)");

            $result['entities_count'] = (int)$cloneQueryBuilder->getQuery()->getSingleScalarResult();
            $maxPages = (!$result['entities_count']) ?: ($result['entities_count'] / $pageSize);
            $result['max_pages'] = ceil($maxPages);
        }

        return $result;
    }
}
