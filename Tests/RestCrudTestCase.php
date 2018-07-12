<?php

namespace Requestum\ApiBundle\Tests;

use Liip\FunctionalTestBundle\Test\WebTestCase;
use Symfony\Bundle\FrameworkBundle\Client;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

abstract class RestCrudTestCase extends WebTestCase
{
    protected $url;
    protected $findOneBy = [];

    protected $headers = [
        'Accept' => 'application/json',
        'HTTP_Authorization' => 'Bearer AccessToken_For_Admin',
    ];

    protected $options = [
        'entity_manager' => 'doctrine'
    ];

    /**
     * @var Client
     */
    protected $client;

    protected function setUp()
    {
        parent::setUp();

        $this->client = static::createClient();
    }

    /**
     * @param null $url
     * @param int $statusCode
     * @return mixed|string
     */
    public function getItem($url = null, $statusCode = Response::HTTP_OK)
    {
        $this->getClient()->request(Request::METHOD_GET, $this->getResourceUrl($url).'/'.$this->getExistedObjectId(), [], [], $this->headers);
        $this->assertEquals($statusCode, $this->getClient()->getResponse()->getStatusCode());

        return $this->getJsonResponse();
    }

    /**
     * @param null $url
     * @param int $statusCode
     * @return mixed|string
     */
    public function getItemWithCriteria($url = null, $criteria = null, $statusCode = Response::HTTP_OK)
    {
        $this->getClient()->request(Request::METHOD_GET, $this->getResourceUrl($url).'/'.$this->getExistedObjectId($criteria), [], [], $this->headers);
        $this->assertEquals($statusCode, $this->getClient()->getResponse()->getStatusCode());

        return $this->getJsonResponse();
    }

    /**
     * @param null $url
     * @param array $filters
     * @param int $statusCode
     * @return mixed|string
     */
    public function getList($url = null, $filters = [], $statusCode = Response::HTTP_OK, $headers = null)
    {
        $headers = $headers ? $headers : $this->headers;
        $this->getClient()->request(Request::METHOD_GET, $this->getResourceUrl($url), $filters, [], $headers);
        $this->assertEquals($statusCode, $this->getClient()->getResponse()->getStatusCode());

        $response = $this->getJsonResponse();

        return $response;
    }

    public static function assertListResult($result, $total, $onPage, $field, $value)
    {
        static::assertArrayHasKey('total', $result);
        static::assertArrayHasKey('entities', $result);
        static::assertEquals($total, $result['total']);
        static::assertCount($onPage, $result['entities']);
        static::assertEquals($value, $result['entities'][0][$field]);
    }

    /**
     * @param $data
     * @param $expectedResponse
     * @param null $url
     * @param int $statusCode
     * @return mixed|string
     */
    public function createItem($data, $url = null, $statusCode = Response::HTTP_CREATED)
    {
        $this->getClient()->request(Request::METHOD_POST, $this->getResourceUrl($url), $data, [], $this->headers);
        $this->assertEquals($statusCode, $this->getClient()->getResponse()->getStatusCode());

        return $this->getJsonResponse();
    }

    /**
     * @param array  $data
     * @param string $url
     * @param int    $statusCode
     */
    public function updateItem($data, $id = null, $url = null, $statusCode = Response::HTTP_OK)
    {
        $id = $id ?: $this->getExistedObjectId();

        $this->getClient()
            ->request(Request::METHOD_PATCH, $this->getResourceUrl($url).'/'.$id, $data, [], $this->headers);

        $this->assertEquals($statusCode, $this->getClient()->getResponse()->getStatusCode());

        return $this->getJsonResponse();
    }

    /**
     * {@inheritdoc}
     */
    public function deleteItem($url = null, $statusCode = Response::HTTP_NO_CONTENT, $softDelete = true)
    {
        $object = $this->getObjectOf($this->getEntityName());
        $id = $object->getId();

        $this->getClient()
            ->request(Request::METHOD_DELETE, $this->getResourceUrl($url).'/'.$id, [], [], $this->headers);
        $this->assertEquals($statusCode, $this->getClient()->getResponse()->getStatusCode());

        $result = $this->getDoctrineManager()->getRepository($this->getEntityName())->find($id);
        
        if ($softDelete) {
            $this->assertNotEquals(null, $result);
            $this->assertInstanceOf(\DateTime::class, $result->getDeletedAt());
        } else {
            $this->assertNull($result);
        }

        return $this->getJsonResponse();
    }

    /**
     * Return name of model.
     *
     * @return string
     */
    abstract protected function getEntityName();

    /**
     * {@inheritdoc}
     */
    protected function getJsonResponse()
    {
        $content = $this->getClient()->getResponse()->getContent();

        if ($content) {
            static::assertJson($content);
            $content = json_decode($content, true);
        }

        return $content;
    }

    /**
     * {@inheritdoc}
     */
    protected function getExistedObjectId($criteria = null)
    {
        $object = $this->getObjectOf($this->getEntityName(), $criteria);

        if (!method_exists($object, 'getId')) {
            static::fail('object doesn\'t have getId method');
        };

        return $object->getId();
    }

    /**
     * {@inheritdoc}
     */
    protected function getObjectOf($class, $criteria = null)
    {
        $criteria = $criteria ?: $this->findOneBy;

        $object = $this->getDoctrineManager()
            ->getRepository($class)
            ->findOneBy($criteria)
        ;

        if (!$object) {
            static::fail('test object not found');
        }

        return $object;
    }

    /**
     * @param null|string $url
     *
     * @return null|string
     */
    protected function getResourceUrl($url = null)
    {
        if (!$url) {
            return $this->url;
        }

        return $url;
    }

    protected function getClient()
    {
        return $this->client;
    }

    /**
     * @return mixed
     * @throws \Exception
     */
    protected function getDoctrineManager()
    {
        switch ($this->options['entity_manager']) {
            case 'doctrine_mongodb':
                $manager = $this->getClient()->getContainer()->get('doctrine_mongodb.odm.default_document_manager');
                break;

            case 'doctrine':
                $manager = $this->getClient()->getContainer()->get('doctrine.orm.default_entity_manager');
                break;

            default:
                throw new \Exception('Entity manager not declared');
                break;
        }

        return $manager;
    }
}