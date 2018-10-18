<?php

namespace Requestum\ApiBundle\Filter;

/**
 * Class EntityContextData
 *
 * @package Requestum\ApiBundle\Action\Extension
 */
class EntityContextData implements ContextDataInterface
{
    private $entity;

    private $field;

    private $filters;

    public function __construct($field, $entity, $filters)
    {
        $this->filters = $filters;
        $this->entity = $entity;
        $this->field = $field;
    }

    public function getFilters()
    {
        return $this->filters;
    }

    public function getValue()
    {
        return $this->entity;
    }

    public function getField()
    {
        return $this->field;
    }
}
