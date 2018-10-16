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

    public function __construct($entity)
    {
        $this->entity = $entity;
    }

    public function getFilters()
    {
        return ['id' => $this->entity->getId()];
    }

    public function getValue()
    {
        return $this->entity;
    }
}
