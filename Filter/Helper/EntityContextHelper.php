<?php

namespace Requestum\ApiBundle\Filter\Helper;

use Doctrine\ORM\EntityManager;

class EntityContextHelper
{
    private $em;

    public function __construct(EntityManager $entityManager)
    {
        $this->em = $entityManager;
    }

    /**
     * @param $entity
     *
     * @return array
     */
    public function getEntityKeys($entity)
    {
        $metadata = $this->em->getClassMetadata(get_class($entity));

        $columns = $metadata->getIdentifier();

        if (isset($metadata->table['uniqueConstraints'])) {
            foreach ($metadata->table['uniqueConstraints'] as $uniqueConstraint) {
                if (isset($uniqueConstraint['columns'])) {
                    $columns += $uniqueConstraint['columns'];
                }
            }
        }

        foreach ($columns as $k => $column) {
            if (isset($metadata->fieldNames[$column])) {
                $columns[$k] = $metadata->fieldNames[$column];
            }
        }

        return $columns;
    }
}
