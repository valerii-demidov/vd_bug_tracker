<?php

namespace Oro\BugTrackerBundle\Twig;

use Symfony\Component\HttpFoundation\RequestStack;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\QueryBuilder;
use Doctrine\ORM\EntityManagerInterface;
use Oro\BugTrackerBundle\Repository\Paginator\PaginatorInterface;

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
    ) {
        $this->request = $request;
        $this->manager = $manager;
    }

    public function getFunctions()
    {
        return [
            new \Twig_SimpleFunction('paginator_object_by_custom_condition', [$this, 'getPaginatorCustomCondition']),
            new \Twig_SimpleFunction('paginator_object_by_entity_class', [$this, 'getPaginatorObjectByEntityClass']),
        ];
    }

    public function getPaginatorObjectByEntityClass($entityClass, $paginatorVar, $pageSize = self::DEFAULT_PAGE_SIZE)
    {
        $result['max_pages'] = 0;
        $result['entity_collection'] =  [];
        $result['entities_count'] = 0;
        $currentRequest = $this->request->getCurrentRequest();

        $entityRepository = $this->manager->getRepository($entityClass);
        if ($entityRepository) {
            $queryBuilder = $entityRepository->createQueryBuilder('entity');
            $currentPage = $currentRequest->get($paginatorVar, 1);
            if ($queryBuilder) {
                if ($entityRepository instanceof PaginatorInterface) {
                    $buildResult = $entityRepository->getCurrentPageByQb($queryBuilder, $currentPage, $pageSize);
                    $result = array_merge($result, $buildResult);
                }
            }
        }

        return $result;
    }

    public function getPaginatorCustomCondition(
        $entityClass,
        $methodName,
        $paginatorVar,
        $methodAttributes,
        $pageSize = self::DEFAULT_PAGE_SIZE
    ) {
        $currentRequest = $this->request->getCurrentRequest();
        $result['max_pages'] = 0;
        $result['entity_collection'] = [];
        $result['entities_count'] = 0;

        $entityRepository = $this->manager->getRepository($entityClass);
        if ($entityRepository instanceof PaginatorInterface) {
            $qb = $entityRepository->getQbByCustomCondition($methodName, $methodAttributes);
            if ($qb && ($qb instanceof QueryBuilder)) {
                $currentPage = (int)$currentRequest->get($paginatorVar, 1);
                $buildResult = $entityRepository->getCurrentPageByQb($qb, $currentPage, $pageSize);
            }

            $result = array_merge($result, $buildResult);
        }

        return $result;
    }
}
