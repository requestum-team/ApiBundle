<?php

namespace Tests\Requestum\ApiBundle\DoctrineExtensions\Query;

class MysqlTestCase extends DbTestCase
{
    /** @var string */
    protected $driver = 'pdo_mysql';
}
