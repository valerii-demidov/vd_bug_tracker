<?php

namespace Oro\BugTrackerBundle\Twig;

use Symfony\Component\HttpFoundation\RequestStack;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\QueryBuilder;
use Doctrine\ORM\EntityManagerInterface;

class PaginatorExtension extends \Twig_Extension
{
    const DEFAULT_PAGE_SIZE = 3;

    /** @var RequestStack */
    protected $request;

    /** @var EntityManagerInterface */
    protected $manager;

    /**
     * PaginatorExtension constructor.
     * @param RequestStack $request
     */
    public function __construct(
        RequestStack $request,
        EntityManagerInterface $manager
    )
    {
        $this->request = $request;
        $this->manager = $manager;
    }

    public function getFunctions()
    {
        return [
            new \Twig_SimpleFunction('paginator_object', [$this, 'getPaginatorObject']),
            new \Twig_SimpleFunction('paginator_object_by_qb', [$this, 'getPaginatorObjectByQb']),
            new \Twig_SimpleFunction('paginator_object_by_entity_class', [$this, 'getPaginatorObjectByEntityClass']),
        ];
    }

    /**
     * @param EntityRepository $entityRepository
     * @param $paginatorVar
     * @return mixed
     */
    public function getPaginatorObject(EntityRepository $entityRepository, $paginatorVar)
    {
        $queryBuilder = $entityRepository->createQueryBuilder('entity');
        $result = $this->getPaginatorObjectByQb($queryBuilder, $paginatorVar);

        return $result;
    }

    /**
     * @param $entityClass
     * @param QueryBuilder $queryBuilder
     * @param $paginatorVar
     * @param int $pageSize
     * @return mixed
     */
    public function getPaginatorObjectByQb(
        $entityClass,
        QueryBuilder $queryBuilder,
        $paginatorVar,
        $pageSize = self::DEFAULT_PAGE_SIZE
    )
    {
        $currentRequest = $this->request->getCurrentRequest();
        $result['max_pages'] = 0;
        $result['entity_collection'] =  [];
        $result['entities_count'] = 0;

        if ($currentRequest && is_object($currentRequest)) {
            $currentPage = (int)$currentRequest->get($paginatorVar);
            $currentPage = ($currentPage) ?: 1;

            $entityRepository = $this->manager->getRepository($entityClass);
            if ($entityRepository) {
                if ($queryBuilder) {
                    if (method_exists($entityRepository, 'buildCurrentPageQb')) {
                        $buildResult = $entityRepository->buildCurrentPageQb($queryBuilder, $currentPage, $pageSize);
                        $result = array_merge($result, $buildResult);
                    }
                }
            }
        }

        return $result;
    }

    public function getPaginatorObjectByEntityClass($entityClass, $paginatorVar, $pageSize = self::DEFAULT_PAGE_SIZE)
    {
        $result['max_pages'] = 0;
        $result['entity_collection'] =  [];
        $result['entities_count'] = 0;

        $entityRepository = $this->manager->getRepository($entityClass);
        if ($entityRepository) {
            $queryBuilder = $entityRepository->createQueryBuilder('entity');
            $result = $this->getPaginatorObjectByQb(
                $entityClass,
                $queryBuilder,
                $paginatorVar,
                $pageSize = self::DEFAULT_PAGE_SIZE
            );
        }

        return $result;
    }
}
