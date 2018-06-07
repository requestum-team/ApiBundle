<?php

namespace Requestum\ApiBundle\Action\Extension;

/**
 * Interface FiltersExtensionInterface
 */
interface FiltersExtensionInterface
{
    /**
     * @param array  $filters
     * @param string $entityClass
     * @param array  $options
     * @return mixed
     */
    public function extend(&$filters, $entityClass, $options = []);
}
