<?php

namespace App\Tests\CommandsChainBundle\EventSubscriber;

use App\CommandsChainBundle\ChainableInterface;
use App\CommandsChainBundle\EvenSubscriber\CommandEventSubscriber;
use App\CommandsChainBundle\RootCommandInterface;
use LogicException;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Event\ConsoleCommandEvent;
use Symfony\Component\Console\Event\ConsoleTerminateEvent;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class CommandEventSubscriberTest extends TestCase
{
    private MockObject | Command $loggerMock;
    private MockObject | InputInterface $inputMock;
    private MockObject | InputInterface $outputMock;
    protected function setUp(): void
    {
        $this->loggerMock = $this->createMock(LoggerInterface::class);
        $this->inputMock = $this->createMock(InputInterface::class);
        $this->outputMock = $this->createMock(OutputInterface::class);
    }

    public function testAfterCommandIfNoApplication(): void
    {
        $commandService = new class('test:chainItem') extends Command implements ChainableInterface {
            public function getRootCommand() : string
            {
                return 'test:root';
            }
            public function execute(InputInterface $input, OutputInterface $output) : int
            {
                return Command::SUCCESS;
            }
        };

        $command = new class('test:root') extends Command implements RootCommandInterface {

        };
        $consoleEvent = new ConsoleTerminateEvent($command, $this->inputMock, $this->outputMock, Command::SUCCESS);
        $subscriber = new CommandEventSubscriber([$commandService], $this->loggerMock);
        $this->expectException(LogicException::class);
        $subscriber->afterCommand($consoleEvent);
    }

    public function testRunRootCommand(): void
    {
        $subscriber = new CommandEventSubscriber([], $this->loggerMock);
        $command = new class('test:root') extends Command implements RootCommandInterface {
            public function execute(InputInterface $input, OutputInterface $output) : int
            {
                $output->writeln('test');
                return Command::SUCCESS;
            }
        };
        $output = $this->createMock(OutputInterface::class);
        $output
            ->expects(self::once())
            ->method('write')
        ;

        $subscriber->runRootCommand(new ConsoleCommandEvent(
            $command,
            $this->createMock(InputInterface::class),
            $output
        ));
    }

    public function testDisableTaggedCommands(): void
    {
        $commandService = new class('test:chainable') extends Command implements ChainableInterface {
            public function getRootCommand() : string
            {
                return 'test:chainable';
            }
            public function execute(InputInterface $input, OutputInterface $output) : int
            {
                return Command::SUCCESS;
            }
        };
        $subscriber = new CommandEventSubscriber([$commandService], $this->loggerMock);
        $this->loggerMock->expects(self::once())->method('error');
        $subscriber->disableTaggedCommands(new ConsoleCommandEvent($commandService, $this->inputMock, $this->outputMock));
    }

    public function testIfNoCommandsForDisabling(): void
    {
        $commandService = new class('test:chainable') extends Command implements ChainableInterface {
            public function getRootCommand() : string
            {
                return 'test:chainable';
            }
            public function execute(InputInterface $input, OutputInterface $output) : int
            {
                return Command::SUCCESS;
            }
        };
        $subscriber = new CommandEventSubscriber([], $this->loggerMock);
        $this->loggerMock
            ->expects(self::never())
            ->method('error')
        ;
        $subscriber->disableTaggedCommands(new ConsoleCommandEvent($commandService, $this->inputMock, $this->outputMock));
    }
}
