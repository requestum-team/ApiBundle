<?php

namespace Requestum\ApiBundle\Filter;

use Requestum\ApiBundle\Filter\Helper\EntityContextHelper;
use Symfony\Component\PropertyAccess\PropertyAccess;

/**
 * Class EntityContextData
 *
 * @package Requestum\ApiBundle\Action\Extension
 */
class EntityContextData implements ContextDataInterface
{
    private $helper;

    private $entity;

    private $filters = null;

    public function __construct($entity, EntityContextHelper $helper)
    {
        $this->helper = $helper;
        $this->entity = $entity;
    }

    public function getFilters()
    {
        if ($this->filters === null) {
            $this->filters = [];
            $propertyAccessor = PropertyAccess::createPropertyAccessor();
            foreach ($this->helper->getEntityKeys($this->entity) as $column) {
                $this->filters[$column] =  $propertyAccessor->getValue($this->entity, $column);
            }
        }

        return $this->filters;
    }

    public function getValue()
    {
        return $this->entity;
    }
}
