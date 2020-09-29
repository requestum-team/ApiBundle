<?php

namespace Requestum\ApiBundle\Serializer\Normalizer;

use Symfony\Component\PropertyAccess\PropertyAccess;
use Symfony\Component\PropertyAccess\PropertyAccessorInterface;

use Requestum\ApiBundle\Rest\Metadata\Reference;
use Requestum\ApiBundle\Rest\ReferenceWrapper;
use Requestum\ApiBundle\Rest\ResourceMetadataFactory;

/**
 * Class AttributeExtractionStrategy
 *
 * @package Requestum\ApiBundle\Rest
 */
class AttributeExtractionStrategy
{

    /**
     * @var PropertyAccessorInterface
     */
    protected $propertyAccessor;

    /**
     * @var ResourceMetadataFactory
     */
    protected $resourseMetadaFactory;

    /**
     * @param ResourceMetadataFactory $resourseMetadaFactory
     */
    public function __construct(ResourceMetadataFactory $resourseMetadaFactory)
    {
        $this->resourseMetadaFactory = $resourseMetadaFactory;

        $this->propertyAccessor = PropertyAccess::createPropertyAccessor();
    }

    /**
     * @param string|object $object
     * @param string        $attribute
     * @param null|string   $format
     * @param array         $context
     *
     * @return mixed
     */
    public function getValue($object, $attribute, $format = null, array $context = array())
    {
        $propertyValue = $this->propertyAccessor->getValue($object, $attribute);

        if (!is_object($propertyValue)) {
            return $propertyValue;
        }

        if ($this->isExpand($attribute, $context)) {
            return new ReferenceWrapper($propertyValue, $attribute);
        }
        
        $propertyMetadata = $this->resourseMetadaFactory->getPropertyMetadata($object, $attribute);

        if (!($referenceMetadata = isset($propertyMetadata[Reference::class]) ? $propertyMetadata[Reference::class] : null)) {
            return $propertyValue;
        }

        $property = $referenceMetadata->field;

        if (isset($context['groups']) && is_array($context['groups'])) {
            if (count(array_intersect($context['groups'], $referenceMetadata->groups)) > 0) {
                return $propertyValue;
            }
        }

        if ($propertyValue instanceof \Traversable) {
            $value = [];

            foreach ($propertyValue as $collection) {
                $value[] = $this->propertyAccessor->getValue($collection, $property);
            }
        } else {
            $value = $this->propertyAccessor->getValue($propertyValue, $property);
        }

        return  $value;
    }

    /**
     * @param string $attribute
     * @param array  $context
     *
     * @return bool
     */
    private function isExpand($attribute, $context)
    {
        $expansions = isset($context['expand']) ? (array) $context['expand'] : [];
        $currentPath = isset($context['path']) ? $context['path'].'.'.$attribute : $attribute;

        foreach ($expansions as $expansionPath) {
            if (strpos($expansionPath, $currentPath) === 0) {
                return true;
            }
        }

        return false;
    }
}
