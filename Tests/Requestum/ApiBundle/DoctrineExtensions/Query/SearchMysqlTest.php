<?php

namespace Tests\Requestum\ApiBundle\DoctrineExtensions\Query;

/**
 * Class SearchMysqlTest
 *
 * @package Requestum\ApiBundle\Tests\DoctrineExtensions\Query
 */
class SearchMysqlTest
{
    public function testSearch()
    {
        $dql = "SELECT d FROM DoctrineExtensions\Tests\Entities\Date d WHERE AT_TIME_ZONE(d.created, :timeZone) < :currentTime";
        $q = $this->entityManager->createQuery($dql);
        $q->setParameter('timeZone', 'UTC');
        $q->setParameter('currentTime', date('Y-m-d H:i:s'));
        $sql = 'SELECT d0_.id AS id_0, d0_.created AS created_1 FROM Date d0_ WHERE d0_.created AT TIME ZONE ? < ?';
        $this->assertEquals($sql, $q->getSql());
    }
}
