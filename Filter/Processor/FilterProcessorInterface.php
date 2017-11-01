<?php

namespace Requestum\ApiBundle\Filter\Processor;

/**
 * Interface FilterProcessorInterface.
 */
interface FilterProcessorInterface
{
    /**
     * @param string $value
     *
     * @return mixed
     */
    public function processFilter($value);
}
