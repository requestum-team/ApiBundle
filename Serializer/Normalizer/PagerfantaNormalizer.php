<?php

namespace Requestum\ApiBundle\Serializer\Normalizer;

use Pagerfanta\Pagerfanta;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareTrait;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

/**
 * Class PagerfantaNormalizer.
 */
class PagerfantaNormalizer implements NormalizerInterface, NormalizerAwareInterface
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
        return is_object($data) && $data instanceof Pagerfanta;
    }

    /**
     * @param Pagerfanta $object
     * @param null       $format
     * @param array      $context
     *
     * @return array
     */
    public function normalize($object, $format = null, array $context = array())
    {
        return [
            'total' => $object->getNbResults(),
            'entities' => $this->normalizer->normalize($object->getCurrentPageResults(), $format, $context),
        ];
    }
}
