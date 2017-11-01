<?php

namespace Requestum\ApiBundle\Filter\Handler;

/**
 * Class AbstractHandler.
 */
abstract class AbstractHandler implements FilterHandlerInterface
{
    /**
     * {@inheritdoc}
     */
    public function stopHandling()
    {
        return true;
    }
}
