<?php

namespace Requestum\ApiBundle\Rest;

use Doctrine\Common\Util\ClassUtils;
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
     * @deprecated use ResourceMetadataFactory::getClassMetadata() instead
     * @param mixed $value
     *
     * @return array
     */
    public function getClassMetada($value)
    {
        return $this->getClassMetadata($value);
    }

    /**
     * @param mixed $value
     *
     * @return array
     */
    public function getClassMetadata($value)
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
     * @param      $object
     * @param      $property
     * @param null $targetMetadata
     *
     * @return array
     */
    public function getPropertyMetadata($object, $property, $targetMetadata = null)
    {
        $loadedClasses = $this->getClassMetadata($object);

        $propertyAnnotations = isset($loadedClasses['properties'][$property]) ? $loadedClasses['properties'][$property] : [];

        if (!$targetMetadata) {
            return $propertyAnnotations;
        }

        return isset($propertyAnnotations[$targetMetadata]) ? $propertyAnnotations[$targetMetadata] : null;
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
            $result[get_class($annotation)] = $annotation;
        }
        return !empty($result) ? $result:null;
    }

    /**
     * Have to copy this method from Symfony\Component\Serializer\Mapping\Factory\ClassResolverTrait
     * as far as this trait is internal :(
     *
     * Gets a class name for a given class or instance.
     *
     * @param mixed $value
     *
     * @return string
     *
     * @throws InvalidArgumentException If the class does not exists
     */
    private function getClass($value)
    {
        if (is_string($value)) {
            if (!class_exists($value) && !interface_exists($value)) {
                throw new InvalidArgumentException(sprintf('The class or interface "%s" does not exist.', $value));
            }

            return ltrim($value, '\\');
        }

        if (!is_object($value)) {
            throw new InvalidArgumentException(sprintf('Cannot create metadata for non-objects. Got: "%s"', gettype($value)));
        }

        return get_class($value);
    }
}
