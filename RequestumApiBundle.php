<?php

namespace Requestum\ApiBundle;

use Symfony\Component\DependencyInjection\Compiler\PassConfig;
use Symfony\Component\HttpKernel\Bundle\Bundle;
use Symfony\Component\DependencyInjection\ContainerBuilder;

use Requestum\ApiBundle\DependencyInjection\Compiler\OverrideServiceCompilerPass;
use Requestum\ApiBundle\DependencyInjection\Compiler\PropertyAccessorCompilerPass;

class RequestumApiBundle extends Bundle
{
    public function build(ContainerBuilder $container)
    {
        $container->addCompilerPass(new OverrideServiceCompilerPass());
        $container->addCompilerPass(new PropertyAccessorCompilerPass(), PassConfig::TYPE_OPTIMIZE);
    }
}