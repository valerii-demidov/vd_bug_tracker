<?php

namespace Oro\BugTrackerBundle\Twig;

use Doctrine\ORM\Tools\Pagination\Paginator;

class PaginatorExtension extends \Twig_Extension
{
    const DEFAULT_PAGE_SIZE = 3;

    public function getFunctions()
    {
        return array(
            new \Twig_SimpleFunction('paginator_object', [$this, 'getPaginatorObject']),
            new \Twig_SimpleFunction('paginator_object_by_qb', [$this, 'getPaginatorObjectByQb']),
        );
    }

    public function getPaginatorObject($entityRepository, $currentPage)
    {
        $queryBuilder = $entityRepository->createQueryBuilder('entity');
        $result = $this->getPaginatorObjectByQb($queryBuilder, $currentPage);

        return $result;
    }

    public function getPaginatorObjectByQb($queryBuilder, $currentPage)
    {
        $entityAlias = current($queryBuilder->getRootAliases());
        $cloneQb = clone $queryBuilder;
        $paginator = new Paginator($queryBuilder, false);

        $entityCollection = $paginator
            ->getQuery()
            ->setFirstResult(self::DEFAULT_PAGE_SIZE * ($currentPage - 1))// Offset
            ->setMaxResults(self::DEFAULT_PAGE_SIZE)
            ->getResult();

        // get collection qty
        $cloneQueryBuilder = $cloneQb;
        $cloneQueryBuilder->select("count($entityAlias.id)");
        $totalCount = $cloneQueryBuilder->getQuery()->getSingleScalarResult();

        $result['max_pages'] = ceil($totalCount / self::DEFAULT_PAGE_SIZE);
        $result['entity_collection'] =  $entityCollection;

        return $result;
    }
}
