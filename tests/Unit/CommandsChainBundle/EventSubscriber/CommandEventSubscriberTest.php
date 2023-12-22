<?php

namespace App\Tests\Unit\CommandsChainBundle\EventSubscriber;

use App\ChainCommandBundle\ChainableInterface;
use App\ChainCommandBundle\CommandsManager;
use App\ChainCommandBundle\EvenSubscriber\CommandEventSubscriber;
use App\ChainCommandBundle\RootCommandInterface;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Event\ConsoleCommandEvent;
use Symfony\Component\Console\Event\ConsoleTerminateEvent;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class CommandEventSubscriberTest extends TestCase
{
    public function testDisableSlaveCommands(): void
    {
        $commandManagerMock = $this->createMock(CommandsManager::class);
        $commandManagerMock->expects(self::once())->method('isSlaveCommand')->willReturn(false);

        $subscriber = new CommandEventSubscriber(
            $this->createMock(LoggerInterface::class),
            $commandManagerMock
        );

        $outputMock = $this->createMock(OutputInterface::class);
        $outputMock
            ->expects(self::never())
            ->method('writeln')
        ;

        $consoleCommandEvent = new ConsoleCommandEvent(
            $this->createMock(Command::class),
            $this->createMock(InputInterface::class),
            $outputMock
        );
        $subscriber->disableSlaveCommands($consoleCommandEvent);
    }

    public function testDisableSlaveCommandsNew(): void
    {
        $commandManagerMock = $this->createMock(CommandsManager::class);
        $commandManagerMock->expects(self::once())->method('isSlaveCommand')->willReturn(true);

        $subscriber = new CommandEventSubscriber(
            $this->createMock(LoggerInterface::class),
            $commandManagerMock
        );

        $outputMock = $this->createMock(OutputInterface::class);
        $outputMock
            ->expects(self::once())
            ->method('writeln')
        ;

        $command = new class extends Command implements ChainableInterface {
            public function getRootCommandName(): string
            {
                return 'root';
            }
        };

        $consoleCommandEvent = new ConsoleCommandEvent(
            $command,
            $this->createMock(InputInterface::class),
            $outputMock
        );
        $subscriber->disableSlaveCommands($consoleCommandEvent);
    }

    public function testRunRootCommand(): void
    {
        $commandManagerMock = $this->createMock(CommandsManager::class);
        $commandManagerMock
            ->expects(self::once())
            ->method('runCommand')
        ;

        $subscriber = new CommandEventSubscriber(
            $this->createMock(LoggerInterface::class),
            $commandManagerMock
        );

        $command  = new class extends Command implements RootCommandInterface {
        };


        $event = new ConsoleCommandEvent(
            $command,
            $this->createMock(InputInterface::class),
            $this->createMock(OutputInterface::class)
        );

        $subscriber->runRootCommand($event);
    }

    public function testNotRunRootCommand(): void
    {
        $commandManagerMock = $this->createMock(CommandsManager::class);
        $commandManagerMock
            ->expects(self::never())
            ->method('runCommand')
        ;

        $subscriber = new CommandEventSubscriber(
            $this->createMock(LoggerInterface::class),
            $commandManagerMock
        );

        $event = new ConsoleCommandEvent(
            $this->createMock(Command::class),
            $this->createMock(InputInterface::class),
            $this->createMock(OutputInterface::class)
        );

        $subscriber->runRootCommand($event);
    }

    public function testRunChainCommandsForRoot(): void
    {
        $commandManagerMock = $this->createMock(CommandsManager::class);
        $commandManagerMock->expects(self::once())->method('executeSlaveCommand');
        $subscriber = new CommandEventSubscriber(
            $this->createMock(LoggerInterface::class),
            $commandManagerMock
        );

        $command = new class extends Command implements RootCommandInterface {
        };
        $subscriber->runChainCommandsForRoot(
            new ConsoleTerminateEvent(
                $command,
                $this->createMock(InputInterface::class),
                $this->createMock(OutputInterface::class),
                Command::SUCCESS
            )
        );
    }

    public function testNotRunChainCommandsForRoot(): void
    {
        $commandManagerMock = $this->createMock(CommandsManager::class);
        $commandManagerMock->expects(self::never())->method('executeSlaveCommand');
        $subscriber = new CommandEventSubscriber(
            $this->createMock(LoggerInterface::class),
            $commandManagerMock
        );

        $subscriber->runChainCommandsForRoot(
            new ConsoleTerminateEvent(
                $this->createMock(Command::class),
                $this->createMock(InputInterface::class),
                $this->createMock(OutputInterface::class),
                Command::SUCCESS
            )
        );
    }
}
