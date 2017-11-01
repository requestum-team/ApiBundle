<?php

namespace Requestum\ApiBundle\Rest;

use Doctrine\Common\Util\ClassUtils;
use Symfony\Component\Serializer\Mapping\Factory\ClassResolverTrait;
use Doctrine\Common\Annotations\Reader;

use Requestum\ApiBundle\Rest\Metadata\Reference;

/**
 * Class ResourseMetadataFactory
 *
 * Custom Metadata Factory for reading annotations by Doctrine AnnotationReader
 *
 * @package Requestum\ApiBundle\Rest
 */
class ResourceMetadataFactory
{
    use ClassResolverTrait;

    /**
     * @var Reader
     */
    private $reader;

    /**
     * @var array
     */
    private $loadedClasses;

    /**
     * @param Reader $reader
     */
    public function __construct(Reader $reader)
    {
        $this->reader = $reader;
    }

    /**
     * {@inheritdoc}
     */
    public function getClassMetada($value)
    {
        $class = ClassUtils::getRealClass($this->getClass($value));

        if (isset($this->loadedClasses[$class])) {
            return $this->loadedClasses[$class];
        }

        $reflectionObject = new \ReflectionClass($class);
        $loadedClasses = $attributesMetadata = [];

        if (null !== ($annotation = $this->arrayLevelShift($this->reader->getClassAnnotations($reflectionObject)))) {
            $loadedClasses = array_merge($loadedClasses, $annotation);
        }

        foreach ($reflectionObject->getProperties(\ReflectionProperty::IS_PUBLIC | \ReflectionProperty::IS_PROTECTED | \ReflectionProperty::IS_PRIVATE) as $reflectionProperty) {
            if (null !== ($annotation = $this->arrayLevelShift($this->reader->getPropertyAnnotations($reflectionProperty)))) {
                $attributesMetadata[$reflectionProperty->getName()] = $annotation;
            }
        }

        $loadedClasses['properties'] = $attributesMetadata;

        return $this->loadedClasses[$class] = $loadedClasses;
    }

    /**
     * @param string|object $object
     * @param string        $property
     *
     * @return array
     */
    public function getPropertyMetadata($object, $property)
    {
        $loadedClasses = $this->getClassMetada($object);

        return isset($loadedClasses['properties'][$property]) ? $loadedClasses['properties'][$property]:[];
    }

    /**
     * @param array $annotations
     *
     * @return array|null
     */
    private function arrayLevelShift($annotations)
    {
        $result = [];

        foreach ($annotations as $annotation) {
            switch (get_class($annotation)) {
                case Reference::class:
                    $result[get_class($annotation)] = $annotation;
                    break;
            }
        }

        return !empty($result) ? $result:null;
    }
}