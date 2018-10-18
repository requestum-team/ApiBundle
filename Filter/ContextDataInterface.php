<?php

namespace Requestum\ApiBundle\Filter;

/**
 * Class ContextDataInterface
 *
 * @package Requestum\ApiBundle\Action\Extension
 */
interface ContextDataInterface
{
    /**
     * @return array
     */
    public function getFilters();

    /**
     * @return \stdClass
     */
    public function getValue();

    /**
     * @return string
     */
    public function getField();
}
