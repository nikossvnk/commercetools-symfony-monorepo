<?php

namespace Commercetools\Symfony\ExampleBundle;

use Commercetools\Symfony\CtpBundle\DependencyInjection\Compiler\ProfilerControllerPass;
use Commercetools\Symfony\ExampleBundle\DependencyInjection\ExampleExtension;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class ExampleBundle extends Bundle
{
    public function getContainerExtension()
    {
        return new ExampleExtension();
    }

    public function build(ContainerBuilder $container)
    {
        $container->addCompilerPass(new ProfilerControllerPass());
    }

}
