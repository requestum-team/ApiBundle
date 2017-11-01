<?php

namespace Requestum\ApiBundle\Tests;

use Liip\FunctionalTestBundle\Test\WebTestCase;
use Symfony\Bundle\FrameworkBundle\Client;
use Symfony\Component\HttpFoundation\Response;

class IsolationWebTestCase extends WebTestCase
{
    use DbIsolationExtensionTrait;

    /**
     * @var Client
     */
    protected $client;

    /**
     * @var Client
     */
    protected static $clientInstance;

    protected function setUp()
    {
        $this->initClient();
        $this->client->setServerParameter('HTTP_HOST', $this->getContainer()->getParameter('base_test_host'));
    }

    protected function tearDown()
    {
        $this->rollbackTransaction();
        unset($this->client);
    }

    /**
     * @return Client
     */
    protected function getClient()
    {
        return $this->client;
    }

    protected function initClient(array $options = [], array $server = [], $force = false)
    {
        if (!self::$clientInstance) {
            if (!isset($options['debug'])) {
                $options['debug'] = false;
            }

            $this->client = self::$clientInstance = static::createClient($options, $server);
            $this->client->disableReboot();

            $this->startTransaction();
        } else {
            self::$clientInstance->setServerParameters($server);
        }

        $this->client = self::$clientInstance;
    }

    protected function getContainer()
    {
        return static::getClientInstance()->getContainer();
    }

    public static function getClientInstance()
    {
        if (!self::$clientInstance) {
            throw new \BadMethodCallException('Client instance is not initialized.');
        }

        return self::$clientInstance;
    }

    protected function getDecodedJsonResponse(Response $response)
    {
        return json_decode($response->getContent(), true);
    }
}