<?php

namespace Tests\Requestum\ApiBundle\DoctrineExtensions\Query;

/**
 * Class SearchPostgresqlTest
 * @package Tests\Requestum\ApiBundle\DoctrineExtensions\Query
 */
class SearchPostgresqlTest extends PostgresqlTestCase
{
    public function testSearch()
    {
        $dql = "SELECT u FROM Tests\Requestum\ApiBundle\DoctrineExtensions\Entities\User u WHERE SEARCH(u.fullName, :value) = true";
        $q = $this->entityManager->createQuery($dql);
        $q->setParameter('value', '%test%');
        $sql = 'SELECT u0_.id AS id_0, u0_.fullName AS fullName_1, u0_.email AS email_1 FROM User u0_ WHERE u0_.fullName ILIKE "%test%"';
        $this->assertEquals($sql, $q->getSql());
    }
}
