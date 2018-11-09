<?php

namespace Requestum\ApiBundle\Pagination;

use Symfony\Component\Serializer\Normalizer\NormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareTrait;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class ApiPaginationNormalizer implements NormalizerInterface, NormalizerAwareInterface
{
    use NormalizerAwareTrait;

    /**
     * @param mixed $data
     * @param null  $format
     *
     * @return bool
     */
    public function supportsNormalization($data, $format = null)
    {
        return is_object($data) && $data instanceof ApiPagination;
    }

    /**
     * @param ApiPagination $object
     * @param null       $format
     * @param array      $context
     *
     * @return array
     */
    public function normalize($object, $format = null, array $context = array())
    {
        $results =
            [
                'total' => $object->getNbResults(),
                'entities' => $this->normalizer->normalize($object->getCurrentPageResults(), $format, $context),
            ];

        $object->getPreviousCursor() ? $results['prevCursor'] = $object->getPreviousCursor() : false;
        $object->getNextCursor() ? $results['nextCursor'] = $object->getNextCursor() : false;

        return $results;
    }
}