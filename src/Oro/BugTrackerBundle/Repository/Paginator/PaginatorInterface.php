<?php

namespace Oro\BugTrackerBundle\Repository\Paginator;

use Doctrine\ORM\QueryBuilder;

interface PaginatorInterface
{
    public function getCurrentPageByQb(QueryBuilder $queryBuilder, $currentPage, $pageSize);

    public function getQbByCustomCondition($method, array $attributes);
}