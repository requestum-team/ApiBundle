<?php

namespace Tests\Requestum\ApiBundle\DoctrineExtensions\Entities;

/**
 * Class User
 *
 * @package Requestum\ApiBundle\Tests\DoctrineExtensions\Entities
 */
class User
{
    /** @Id @Column(type="string") @GeneratedValue */
    public $id;

    /**
     * @Column(type="string")
     */
    public $fullName;

    /**
     * @Column(type="string")
     */
    public $email;
}
