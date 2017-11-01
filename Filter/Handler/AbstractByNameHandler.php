<?php

namespace Requestum\ApiBundle\Filter\Handler;

/**
 * Class ConcreteHandler.
 */
abstract class AbstractByNameHandler extends AbstractHandler
{
    /**
     * {@inheritdoc}
     */
    public function supports($filter, $value)
    {
        return $filter === $this->getFilterKey();
    }

    /**
     * @return mixed
     */
    abstract protected function getFilterKey();
}
