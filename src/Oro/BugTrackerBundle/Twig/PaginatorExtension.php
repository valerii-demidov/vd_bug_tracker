<?php

namespace Oro\BugTrackerBundle\Twig;

use Symfony\Component\HttpFoundation\RequestStack;

class PaginatorExtension extends \Twig_Extension
{
    const DEFAULT_PAGE_SIZE = 3;

    /**
     * @var Request
     */
    protected $request;

    /**
     * PaginatorExtension constructor.
     * @param RequestStack $request
     */
    public function __construct(RequestStack $request)
    {
        $this->request = $request;
    }

    public function getFunctions()
    {
        return array(
            new \Twig_SimpleFunction('paginator_object', [$this, 'getPaginatorObject']),
            new \Twig_SimpleFunction('paginator_object_by_qb', [$this, 'getPaginatorObjectByQb']),
        );
    }

    public function getPaginatorObject($entityRepository, $paginatorVar)
    {
        $queryBuilder = $entityRepository->createQueryBuilder('entity');
        $result = $this->getPaginatorObjectByQb($queryBuilder, $paginatorVar);

        return $result;
    }

    public function getPaginatorObjectByQb($queryBuilder, $paginatorVar, $pageSize = self::DEFAULT_PAGE_SIZE)
    {
        $currentPage = (int)$this->request->getCurrentRequest()->get($paginatorVar);
        $currentPage = ($currentPage) ?: 1;
        $entityAlias = current($queryBuilder->getRootAliases());
        $cloneQb = clone $queryBuilder;

        $entityCollection = $queryBuilder
            ->getQuery()
            ->setFirstResult($pageSize * ($currentPage - 1))// Offset
            ->setMaxResults($pageSize)
            ->getResult();

        // get collection qty
        $cloneQueryBuilder = $cloneQb;
        $cloneQueryBuilder->select("count($entityAlias.id)");
        $totalCount = $cloneQueryBuilder->getQuery()->getSingleScalarResult();

        $result['max_pages'] = ceil($totalCount / $pageSize);
        $result['entity_collection'] =  $entityCollection;
        $result['count'] = $totalCount;

        return $result;
    }
}
