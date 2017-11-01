<?php

namespace Requestum\ApiBundle\Action\Extension;

/**
 * Interface FiltersExtensionInterface
 */
interface FiltersExtensionInterface
{
    /**
     * @param $filters
     * @return mixed
     */
    public function extend(&$filters);
}