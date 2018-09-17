<?php

namespace Requestum\ApiBundle\Rest\Metadata;

use Doctrine\Common\Annotations\Annotation;

/**
 * Access annotation to delegate making a decision to normalize the field in the access decision manager
 *
 * @Annotation
 *
 * @Target("PROPERTY")
 */
final class Access extends Annotation
{
}
