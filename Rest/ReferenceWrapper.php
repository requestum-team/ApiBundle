<?php

namespace Requestum\ApiBundle\Rest;

class ReferenceWrapper
{

    /**
     * @var object
     */
    private $object;

    /**
     * @var string
     */
    private $attribute;

    /**
     * @param object $object
     * @param string $attribute
     */
    public function __construct($object, $attribute)
    {
        $this->object = $object;
        $this->attribute = $attribute;
    }

    /**
     * @return mixed
     */
    public function getObject()
    {
        return $this->object;
    }

    /**
     * @param mixed $object
     *
     * @return ReferenceWrapper
     */
    public function setObject($object)
    {
        $this->object = $object;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getAttribute()
    {
        return $this->attribute;
    }

    /**
     * @param mixed $attribute
     *
     * @return ReferenceWrapper
     */
    public function setAttribute($attribute)
    {
        $this->attribute = $attribute;

        return $this;
    }
}