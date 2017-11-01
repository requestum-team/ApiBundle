<?php

namespace Requestum\ApiBundle\Serializer\Normalizer;

use Requestum\ApiBundle\Rest\ReferenceWrapper;
use Doctrine\ORM\Proxy\Proxy;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer as BaseObjectNormalizer;

/**
 * Class ObjectNormalizer
 *
 * Custom Object Normalizer for Symfony Serializer
 *
 * @package Requestum\ApiBundle\Serializer\Normalizer
 */
class ObjectNormalizer extends BaseObjectNormalizer
{

    /**
     * @var AttributeExtractionStrategy
     */
    protected $attributeExtractionStrategy;

    /**
     * @param AttributeExtractionStrategy $attributeExtractionStrategy
     *
     * @return $this
     */
    public function setAttributeExtractionStrategy(AttributeExtractionStrategy $attributeExtractionStrategy)
    {
        $this->attributeExtractionStrategy = $attributeExtractionStrategy;

        return $this;
    }

    /**
     * @param string|object $object
     * @param string        $attribute
     * @param null|string   $format
     * @param array         $context
     *
     * @return mixed
     */
    protected function getAttributeValue($object, $attribute, $format = null, array $context = array())
    {
        return $this->attributeExtractionStrategy->getValue($object, $attribute, $format, $context);
    }

    /**
     * {@inheritdoc}
     */
    public function normalize($object, $format = null, array $context = array())
    {
        if ($object instanceof Proxy) {
            $object->__load();
        }

        $current = null;
        if ($object instanceof ReferenceWrapper) {
            $current = $object->getAttribute();
            $context['path'] = isset($context['path']) ? $context['path'].'.'.$current : $current;

            return $this->serializer->normalize($object->getObject(), $format, $context);
        }

        return parent::normalize($object, $format, $context);
    }
}