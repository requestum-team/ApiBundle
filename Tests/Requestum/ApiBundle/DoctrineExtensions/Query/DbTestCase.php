<?php

namespace Tests\Requestum\ApiBundle\DoctrineExtensions\Query;

use Doctrine\Common\Cache\ArrayCache;
use Doctrine\ORM\Configuration;
use Doctrine\ORM\EntityManager;
use PHPUnit\Framework\TestCase;

/**
 * Class DbTestCase
 * @package Tests\Requestum\ApiBundle\DoctrineExtensions\Query
 */
class DbTestCase extends TestCase
{
    /** @var EntityManager */
    public $entityManager;

    /** @var Configuration */
    protected $configuration;

    /** @var string */
    protected $driver;

    public function setUp(): void
    {
        $this->configuration = new Configuration();
        $this->configuration->setMetadataCacheImpl(new ArrayCache());
        $this->configuration->setQueryCacheImpl(new ArrayCache());
        $this->configuration->setProxyDir(__DIR__ . '/Proxies');
        $this->configuration->setProxyNamespace('DoctrineExtensions\Tests\Proxies');
        $this->configuration->setAutoGenerateProxyClasses(true);
        $this->configuration->setMetadataDriverImpl($this->configuration->newDefaultAnnotationDriver(__DIR__ . '/../Entities'));
        $this->entityManager = EntityManager::create(['driver' => $this->driver, 'memory' => true ], $this->configuration);

        $this->configuration->addCustomStringFunction('SEARCH', 'AppBundle\DoctrineExtensions\Query\Search');
    }

    public function assertDqlProducesSql($actualDql, $expectedSql, $params = [])
    {
        $q = $this->entityManager->createQuery($actualDql);

        foreach ($params as $key => $value) {
            $q->setParameter($key, $value);
        }

        $actualSql = $q->getSql();

        $this->assertEquals($expectedSql, $actualSql);
    }
}
