<?php

namespace Requestum\ApiBundle\Rest\Metadata;

use Doctrine\Common\Annotations\Annotation;

/**
 * Custom annotation for reference expansion
 *
 * @Annotation
 * @Target("PROPERTY")
 */
final class Reference
{
    /**
     * @var string
     */
    public $field = 'id';
}
