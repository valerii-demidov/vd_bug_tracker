<?php

namespace Oro\BugTrackerBundle\Repository;

use Doctrine\ORM\EntityRepository;

/**
 * ProjectRepository
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class CustomerRepository extends EntityRepository
{
    /**
     * Example for $conditionCollection = ['username' => ['like' => 'adm']]
     *
     * @param array $conditionCollection
     * @param bool $isSingleResult
     * @return array|mixed
     */
    public function findByCondition(array $conditionCollection)
    {

        $customerQb = $this->createQueryBuilder('customer');
        $paramInc = 0;
        foreach ($conditionCollection as $fieldName => $fieldConditions) {
            $condInc = 0;
            foreach ($fieldConditions as $conditionName => $conditionValue) {
                $parameterName = 'param'.$paramInc;
                $query = "customer.$fieldName $conditionName :$parameterName";
                (!$condInc) ? $customerQb->where($query) : $customerQb->andWhere($query);
                $customerQb->setParameter($parameterName, $conditionValue);
                $condInc++;
            }
            $paramInc++;
        }

        $customerQbQuery = $customerQb->getQuery();

        return $customerQbQuery->getResult();
    }

    /**
     * @param array $objectCollection
     * @param array $fields
     *
     * @return array
     */
    public function convertCollectionToAssoc($objectCollection, $fields)
    {
        $result = [];
        $inc = 0;
        foreach ($objectCollection as $object) {
            foreach ($fields as $field) {
                $result[$inc][$field] = '';
                if ((is_object($object))) {
                    $getFieldNameFunction = 'get'.ucfirst($field);
                    if (method_exists($object, $getFieldNameFunction)) {
                        $result[$inc][$field] = $object->$getFieldNameFunction();
                    }
                } elseif (is_array($object)) {
                    $result[$inc][$field] = $object[$field];
                }
            }
            $inc++;
        }

        return $result;
    }
}