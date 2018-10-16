<?php

namespace Requestum\ApiBundle\Repository;

use Doctrine\ORM\QueryBuilder;
use Requestum\ApiBundle\Filter\FilterExpander;
use Requestum\ApiBundle\Filter\Handler\CommonHandler;
use Requestum\ApiBundle\Filter\Handler\FilterHandlerInterface;
use Requestum\ApiBundle\Filter\Handler\OrderHandler;

/**
 * Class ApiRepositoryTrait.
 */
trait ApiRepositoryTrait
{

    /**
     * {@inheritdoc}
     */
    public function filter(array $filters, QueryBuilder $builder = null)
    {
        $filterExpander = new FilterExpander();

        $filters = $filterExpander->expand($filters);

        if (!$builder) {
            $builder = $this->createQueryBuilder('e');
        }

        $handlers = $this->createHandlers();

        if ($this->isUseOrderHandler()) {
            $handlers[] = new OrderHandler();
        }

        if ($this->isUseCommonHandler()) {
            $handlers[] = new CommonHandler('e', $this->getPathAliases());
        }

        foreach ($filters as $filter => $value) {
            /** @var FilterHandlerInterface $handler */
            foreach ($handlers as $handler) {
                if ($handler->supports($filter, $value)) {
                    $handler->handle($builder, $filter, $value);

                    if ($handler->stopHandling()) {
                        continue 2;
                    }
                }
            }
        }
        return $builder;
    }

    /**
     * @return array
     */
    protected function getPathAliases()
    {
        return [];
    }

    /**
     * @return bool
     */
    protected function isUseOrderHandler()
    {
        return true;
    }

    /**
     * @return bool
     */
    protected function isUseCommonHandler()
    {
        return true;
    }

    /**
     * @return array
     */
    protected function createHandlers()
    {
        return [];
    }
}
