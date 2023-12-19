<?php

namespace App\CommandsChainBundle;

use App\CommandsChainBundle\DependencyInjection\CompilerPass\DeleteCommandCompilerPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class AppCommandsChainBundle extends Bundle
{
    public function build(ContainerBuilder $container)
    {
        $container->addCompilerPass(new DeleteCommandCompilerPass());
        parent::build($container);
    }

}