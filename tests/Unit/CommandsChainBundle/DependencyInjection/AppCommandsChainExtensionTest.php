<?php

namespace App\Tests\Unit\CommandsChainBundle\DependencyInjection;

use App\ChainCommandBundle\DependencyInjection\AppChainCommandExtension;
use App\ChainCommandBundle\DependencyInjection\AppCommandsChainExtension;
use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class AppCommandsChainExtensionTest extends TestCase
{
    public function testLoad(): void
    {
        $container = new ContainerBuilder();

        $extension = new AppChainCommandExtension();
        $extension->load([], $container);

        self::assertNotEmpty($container->getDefinitions());
    }
}
