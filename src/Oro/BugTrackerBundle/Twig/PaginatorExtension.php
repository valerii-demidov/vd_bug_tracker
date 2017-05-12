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
        );
    }

    /*public function getPaginatorObject()*/
    public function getPaginatorObject($entityRepository, $currentPage)
    {
        $cloneEntityRepository = clone $entityRepository;
        $queryBuilder = $entityRepository->createQueryBuilder('entity');

        $paginator = new Paginator($queryBuilder, false);

        $entityCollection = $paginator
            ->getQuery()
            ->setFirstResult(self::DEFAULT_PAGE_SIZE * ($currentPage - 1))// Offset
            ->setMaxResults(self::DEFAULT_PAGE_SIZE)
            ->getResult();

        // get collection qty
        $cloneQueryBuilder = $cloneEntityRepository->createQueryBuilder('entity');
        $cloneQueryBuilder->select('count(entity.id)');
        $totalCount = $cloneQueryBuilder->getQuery()->getSingleScalarResult();

        $result['max_pages'] = ceil($totalCount / self::DEFAULT_PAGE_SIZE);
        $result['entity_collection'] =  $entityCollection;

        return $result;
    }
}
