<?php

namespace App\Tests\Unit\CommandsChainBundle\EventSubscriber;

use App\CommandsChainBundle\ChainableInterface;
use App\CommandsChainBundle\EvenSubscriber\CommandEventSubscriber;
use App\CommandsChainBundle\RootCommandInterface;
use LogicException;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Event\ConsoleCommandEvent;
use Symfony\Component\Console\Event\ConsoleTerminateEvent;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\BufferedOutput;
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

    public function testRunChainCommandsForRootIfNoApplication(): void
    {
        $commandService = new class ('test:chainItem') extends Command implements ChainableInterface {
            public function getRootCommand(): string
            {
                return 'test:root';
            }
            public function execute(InputInterface $input, OutputInterface $output): int
            {
                return Command::SUCCESS;
            }
        };

        $command = new class ('test:root') extends Command implements RootCommandInterface {
        };
        $consoleEvent = new ConsoleTerminateEvent($command, $this->inputMock, $this->outputMock, Command::SUCCESS);
        $subscriber = new CommandEventSubscriber([$commandService], $this->loggerMock);
        $this->expectException(LogicException::class);
        $subscriber->runChainCommandsForRoot($consoleEvent);
    }

    public function testRunChainCommandsForRoot(): void
    {
        $slaveCommandService = new class ('test:chainItem') extends Command implements ChainableInterface {
            public function getRootCommand(): string
            {
                return 'test:master';
            }
            public function execute(InputInterface $input, OutputInterface $output): int
            {
                $output->writeln('test:slave');
                return Command::SUCCESS;
            }
        };

        $application = $this->createMock(Application::class);
        $masterCommand = new class ('test:master', $application) extends Command implements RootCommandInterface {
            private Application $application;
            public function __construct(string $name, Application $application)
            {
                $this->application = $application;
                parent::__construct($name);
            }
            public function getApplication(): ?Application
            {
                return $this->application;
            }
        };

        $consoleEvent = new ConsoleTerminateEvent($masterCommand, $this->inputMock, $this->outputMock, Command::SUCCESS);
        $subscriber = new CommandEventSubscriber([$slaveCommandService], $this->loggerMock);
        $this->loggerMock
            ->expects(self::atLeast(1))
            ->method('debug')
        ;

        $output = new BufferedOutput();
        $output->writeln('test:slave');
        $this->outputMock
            ->expects(self::once())
            ->method('write')
            ->with($output->fetch())
        ;
        $subscriber->runChainCommandsForRoot($consoleEvent);
    }


    public function testRunRootCommand(): void
    {
        $subscriber = new CommandEventSubscriber([], $this->loggerMock);
        $command = new class ('test:root') extends Command implements RootCommandInterface {
            public function execute(InputInterface $input, OutputInterface $output): int
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

    public function testDisableSlaveCommands(): void
    {
        $commandService = new class ('test:chainable') extends Command implements ChainableInterface {
            public function getRootCommand(): string
            {
                return 'test:chainable';
            }
            public function execute(InputInterface $input, OutputInterface $output): int
            {
                return Command::SUCCESS;
            }
        };
        $subscriber = new CommandEventSubscriber([$commandService], $this->loggerMock);
        $this->loggerMock
            ->expects(self::once())
            ->method('error')
        ;
        $subscriber->disableSlaveCommands(new ConsoleCommandEvent($commandService, $this->inputMock, $this->outputMock));
    }

    public function testIfNoCommandsForDisabling(): void
    {
        $commandService = new class ('test:chainable') extends Command implements ChainableInterface {
            public function getRootCommand(): string
            {
                return 'test:chainable';
            }
            public function execute(InputInterface $input, OutputInterface $output): int
            {
                return Command::SUCCESS;
            }
        };
        $subscriber = new CommandEventSubscriber([], $this->loggerMock);
        $this->loggerMock
            ->expects(self::never())
            ->method('error')
        ;
        $subscriber->disableSlaveCommands(new ConsoleCommandEvent($commandService, $this->inputMock, $this->outputMock));
    }
}
