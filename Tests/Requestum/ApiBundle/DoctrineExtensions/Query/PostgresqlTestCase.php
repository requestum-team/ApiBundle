<?php

namespace Tests\Requestum\ApiBundle\DoctrineExtensions\Query;

class PostgresqlTestCase extends DbTestCase
{

    /** @var string */
    protected $driver = 'pdo_pgsql';
}
