<?php

namespace Tests\Requestum\ApiBundle\Rest;

use PHPUnit\Framework\TestCase;
use Doctrine\Common\Annotations\AnnotationReader;

use Requestum\ApiBundle\Rest\ResourceMetadataFactory;
use Requestum\ApiBundle\Rest\Metadata\Reference;

class TestObject
{

    /**
     * @Reference
     */
    public $id;

    /**
     * @Reference(field="name")
     */
    public $name;
}

class ResourceMetadataFactoryTest extends TestCase
{
    /**
     * @var ResourceMetadataFactory
     */
    private $resourceMetadataFactory;

    protected function setUp(): void
    {
        $this->resourceMetadataFactory = new ResourceMetadataFactory(new AnnotationReader);
    }

    public function testGetClassMetada()
    {
        $testObject = new TestObject();

        $classMetadata = $this->resourceMetadataFactory->getClassMetadata($testObject);

        static::assertArrayHasKey('properties', $classMetadata);
        static::assertArrayHasKey('id', $classMetadata['properties']);
        static::assertArrayHasKey('name', $classMetadata['properties']);

        static::assertInstanceOf(Reference::class, $classMetadata['properties']['id'][Reference::class]);
        static::assertInstanceOf(Reference::class, $classMetadata['properties']['name'][Reference::class]);

        static::assertEquals('id', $classMetadata['properties']['id'][Reference::class]->field);
        static::assertEquals('name', $classMetadata['properties']['name'][Reference::class]->field);
    }

    public function testGetPropertyMetadata()
    {
        $testObject = new TestObject();

        $propertyMetadata = $this->resourceMetadataFactory->getPropertyMetadata($testObject, 'id');

        static::assertInstanceOf(Reference::class, $propertyMetadata[Reference::class]);
        static::assertEquals('id', $propertyMetadata[Reference::class]->field);
    }
}
