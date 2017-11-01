<?php

namespace Requestum\ApiBundle\Repository;

use Doctrine\ORM\QueryBuilder;

/**
 * Interface FilterableRepositoryInterface
 */
interface FilterableRepositoryInterface
{
    /**
     * @param array $filters
     *
     * @return QueryBuilder
     */
    public function filter(array $filters, QueryBuilder $builder = null);
}
