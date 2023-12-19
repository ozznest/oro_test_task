<?php

namespace App\CommandsChainBundle\DependencyInjection\CompilerPass;

use App\BarBundle\Command\BarCommand;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class DeleteCommandCompilerPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container)
    {
        //$definition = $container->getDefinition(BarCommand::class);
        //$definition->clearTag('console.command');
    }
}