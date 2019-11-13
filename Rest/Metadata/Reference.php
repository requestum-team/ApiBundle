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

    /**
     * @var string[]
     */
    public $groups;

    /**
     * @param array $data
     *
     * @throws InvalidArgumentException
     */
    public function __construct(array $data)
    {
        $groups = isset($data['groups']) ? (array) $data['groups'] : [];

        foreach ($groups as $group) {
            if (!\is_string($group)) {
                throw new InvalidArgumentException(sprintf('Parameter of annotation "%s" must be a string or an array of strings.', \get_class($this)));
            }
        }

        $this->groups = $groups;
    }
}