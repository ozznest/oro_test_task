<?php

namespace App\Tests\CommandsChainBundle\DependencyInjection;

use App\CommandsChainBundle\DependencyInjection\AppCommandsChainExtension;
use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class AppCommandsChainExtensionTest extends TestCase
{
    public function testLoad(): void{
        $container = new ContainerBuilder();

        $extension = new AppCommandsChainExtension();
        $extension->load([], $container);

        self::assertNotEmpty($container->getDefinitions());
    }
}