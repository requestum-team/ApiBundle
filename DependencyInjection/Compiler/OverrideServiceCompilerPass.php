<?php

namespace Requestum\ApiBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

use Requestum\ApiBundle\Serializer\Normalizer\ObjectNormalizer;

class OverrideServiceCompilerPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container)
    {
        $definition = $container->getDefinition('serializer.normalizer.object');
        $definition
            ->setClass(ObjectNormalizer::class)
            ->addMethodCall(
                'setAttributeExtractionStrategy',
                [
                    $container->getDefinition('core.resourse.attribute_extraction_strategy')
                ]
            )
        ;

        $taggedServices = $container->findTaggedServiceIds('action.subresource');
        $referenceRequestStack = new Reference('request_stack');
        foreach ($taggedServices as $id => $tags) {
            foreach ($tags as $tagParams) {
                $serviceDefinition =  $container->findDefinition($id);
                $serviceDefinition
                    ->addMethodCall('initContextFilter', [$tagParams, $referenceRequestStack]);
                ;
            }
        }

    }
}