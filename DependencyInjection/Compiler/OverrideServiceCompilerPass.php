<?php

namespace Requestum\ApiBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

use Requestum\ApiBundle\Serializer\Normalizer\ObjectNormalizer;

/**
 * Class OverrideServiceCompilerPass
 */
class OverrideServiceCompilerPass implements CompilerPassInterface
{
    /**
     * @param ContainerBuilder $container
     */
    public function process(ContainerBuilder $container)
    {
        $definition = $container->getDefinition('serializer.normalizer.object');
        $definition
            ->setClass(ObjectNormalizer::class)
            ->addMethodCall(
                'setAttributeExtractionStrategy',
                [
                    $container->getDefinition('core.resourse.attribute_extraction_strategy'),
                ]
            )
            ->addMethodCall(
                'setAuthorizationChecker',
                [
                    $container->getDefinition('security.authorization_checker'),
                ]
            )
            ->addMethodCall(
                'setAnnotationReader',
                [
                    $container->getDefinition('core.resourse.metadata_factory'),
                ]
            )
        ;
    }
}