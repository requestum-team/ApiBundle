<?php

namespace Tests\Requestum\ApiBundle\Filter;

use Requestum\ApiBundle\Filter\FilterExpander;
use PHPUnit\Framework\TestCase;

class FilterExpanderTest extends TestCase
{
    public function testExpand()
    {
        $filters = [
            'suiteUser.suite.building' => 7,
            'suiteUser.suite.suiteNumber' => 10,
        ];

        $expanded = [
            'suiteUser' => [
                'suite' => [
                    'building' => 7,
                    'suiteNumber' => 10
                ]
            ]
        ];

        $expander = new FilterExpander();
        $result = $expander->expand($filters);

        static::assertEquals($expanded, $result);
    }
}