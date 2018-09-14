<?php

namespace Requestum\ApiBundle\Rest\Metadata;

use Doctrine\Common\Annotations\Annotation;

/**
 * Access annotation for hiding property when normalization object for user who does not own object
 *
 * @Annotation
 *
 * @Target("PROPERTY")
 */
final class Access extends Annotation
{
}
