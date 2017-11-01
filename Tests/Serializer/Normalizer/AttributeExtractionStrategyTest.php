<?php

namespace Requestum\ApiBundle\Tests\Serializer\Normalizer;


use PHPUnit\Framework\TestCase;
use Doctrine\Common\Annotations\AnnotationReader;

use Requestum\ApiBundle\Rest\ResourceMetadataFactory;
use Requestum\ApiBundle\Serializer\Normalizer\AttributeExtractionStrategy;
use Requestum\ApiBundle\Rest\Metadata\Reference;
use Requestum\ApiBundle\Rest\ReferenceWrapper;

class TestObject
{

    /**
     * @var integer
     */
    public $id;

    /**
     * @var \stdClass
     */
    public $simpleObject;

    /**
     * @var TestObject
     *
     * @Reference
     */
    public $referenceObject;

    /**
     * @param integer              $id
     * @param \stdClass            $simpleObject
     * @param TestObject|\stdClass $referenceObject
     */
    public function fill($id, $simpleObject, $referenceObject)
    {
        $this->id = $id;
        $this->simpleObject = $simpleObject;
        $this->referenceObject = $referenceObject;
    }
}

class AttributeExtractionStrategyTest extends TestCase
{

    /**
     * @var AttributeExtractionStrategy
     */
    private $attributeExtractionStrategy;

    /**
     * @var TestObject
     */
    private $referenceObject;

    /**
     * @var TestObject
     */
    private $testObject;

    protected function setUp()
    {
        $resourceMetadataFactory = $this->createMock(ResourceMetadataFactory::class);

        $resourceMetadataFactory
            ->method('getPropertyMetadata')
            ->willReturnCallback(function($object, $attribute) {
                if ($attribute == 'referenceObject') {
                    return [
                        Reference::class => new Reference()
                    ];
                }

                return [];
            })
        ;

        $this->attributeExtractionStrategy = new AttributeExtractionStrategy($resourceMetadataFactory);

        $std = new \stdClass;
        $std->id = 123;

        $this->referenceObject = new TestObject();
        $this->referenceObject->fill(1, $std, $std);

        $this->testObject = new TestObject();
        $this->testObject->fill(2, $std, $this->referenceObject);
    }

    public function testGetValueScalar()
    {
        $propertyValue = $this->attributeExtractionStrategy->getValue($this->testObject, 'id');
        static::assertInternalType('integer', $propertyValue);
    }

    public function testGetValueSimpleObject()
    {
        $propertyValue = $this->attributeExtractionStrategy->getValue($this->testObject, 'simpleObject');
        static::assertInstanceOf(\stdClass::class, $propertyValue);
    }

    public function testGetValueReferenceObject()
    {
        $context = [
            'expand' => [
                'ibdObject'
            ]
        ];

        $propertyValue = $this->attributeExtractionStrategy->getValue($this->testObject, 'referenceObject', null, $context);
        static::assertEquals($this->referenceObject->id, $propertyValue);
    }

    /**
     * @dataProvider contextProvider
     */
    public function testGetValueExpandedObject($context)
    {
        $propertyValue = $this->attributeExtractionStrategy->getValue($this->testObject, 'referenceObject', null, $context);
        static::assertInstanceOf(ReferenceWrapper::class, $propertyValue);
        static::assertInstanceOf(TestObject::class, $propertyValue->getObject());
        static::assertEquals('referenceObject', $propertyValue->getAttribute());
        static::assertEquals($this->referenceObject->id, $propertyValue->getObject()->id);
    }

    public function testGetValueExpandedPathObject()
    {
        $context = [
            'expand' => [
                'referenceObject'
            ]
        ];

        $referenceWrapper = $this->attributeExtractionStrategy->getValue($this->testObject, 'referenceObject', null, $context);
        static::assertInstanceOf(ReferenceWrapper::class, $referenceWrapper);

        $expandContext = [
            'expand' => [
                'referenceObject.referenceObject'
            ],
            'path' => $referenceWrapper->getAttribute()
        ];

        $propertyValue = $this->attributeExtractionStrategy->getValue($referenceWrapper->getObject(), 'referenceObject', null, $expandContext);
        static::assertInstanceOf(ReferenceWrapper::class, $propertyValue);
        static::assertInstanceOf(\stdClass::class, $propertyValue->getObject());
        static::assertEquals('referenceObject', $propertyValue->getAttribute());
        static::assertEquals(123, $propertyValue->getObject()->id);
    }

    public function contextProvider()
    {
        return [
            [
                [
                    'expand' => [
                        'referenceObject'
                    ]
                ]
            ],
            [
                [
                    'expand' => [
                        'referenceObject.simpleObject'
                    ]
                ]
            ],
        ];
    }
}