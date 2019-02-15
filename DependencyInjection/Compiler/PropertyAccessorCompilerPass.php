<?php

namespace Requestum\ApiBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Requestum\ApiBundle\Serializer\Normalizer\ObjectNormalizer;

/**
 * Class OverrideServiceCompilerPass
 */
class PropertyAccessorCompilerPass implements CompilerPassInterface
{
    /**
     * @param ContainerBuilder $container
     */
    public function process(ContainerBuilder $container)
    {
        $propertyAccessorDefinition = $container->getDefinition('property_accessor');
        $propertyAccessorDefinition->setPublic(true);
        $container->setDefinition('property_accessor', $propertyAccessorDefinition);
    }
}