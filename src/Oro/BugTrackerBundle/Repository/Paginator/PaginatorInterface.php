<?php

namespace Oro\BugTrackerBundle\Repository\Paginator;

use Doctrine\ORM\QueryBuilder;

interface PaginatorInterface
{
    /**
     * @param QueryBuilder $queryBuilder
     * @param $currentPage
     * @param $pageSize
     * @return mixed
     */
    public function getCurrentPageByQb(QueryBuilder $queryBuilder, $currentPage, $pageSize);

    /**
     * @param $method
     * @param array $attributes
     * @return mixed
     */
    public function getQbByCustomCondition($method, array $attributes);
}
