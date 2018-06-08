<?php

namespace Requestum\ApiBundle\Action\Extension;

use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class OptionsExtensionInterface
 */
interface OptionsExtensionInterface
{
    /**
     * @param OptionsResolver $resolver
     */
    public function setOptionDefaults(OptionsResolver $resolver);
}
