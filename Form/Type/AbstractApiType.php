<?php

namespace Requestum\ApiBundle\Form\Type;

use Symfony\Component\Form\AbstractType;

/**
 * Class AbstractApiType
 */
class AbstractApiType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return '';
    }
}