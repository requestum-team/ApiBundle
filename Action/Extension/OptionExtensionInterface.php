<?php

namespace Requestum\ApiBundle\Action\Extension;

use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class OptionExtensionInterface
 */
interface OptionExtensionInterface
{
    /**
     * @param OptionsResolver $resolver
     */
    public function setOptionDefaults(OptionsResolver $resolver);
}
