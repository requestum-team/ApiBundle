<?php

namespace Requestum\ApiBundle\Filter\Handler;

use Requestum\ApiBundle\Filter\Exception\BadFilterException;
use Doctrine\ORM\QueryBuilder;

/**
 * Interface HandlerInterface.
 */
interface FilterHandlerInterface
{
    /**
     * @param string $filter
     * @param mixed  $value
     *
     * @throws BadFilterException
     *
     * @return bool
     */
    public function supports($filter, $value);

    /**
     * @param QueryBuilder $builder
     * @param string       $filter
     * @param mixed        $value
     *
     * @throws BadFilterException
     */
    public function handle(QueryBuilder $builder, $filter, $value);

    /**
     * @return bool
     */
    public function stopHandling();
}
