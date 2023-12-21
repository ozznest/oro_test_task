<?php

namespace App\Tests\Unit\CommandsChainBundle;

use App\CommandsChainBundle\ChainableInterface;
use App\CommandsChainBundle\CommandsManager;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\BufferedOutput;
use Symfony\Component\Console\Output\OutputInterface;

class CommandsManagerTest extends TestCase
{
    private MockObject|LoggerInterface $loggerMock;
    private MockObject|BufferedOutput $bufferedOutputMock;

    protected function setUp(): void
    {
        $this->loggerMock = $this->createMock(LoggerInterface::class);
        $this->bufferedOutputMock = $this->createMock(BufferedOutput::class);
        parent::setUp();
    }

    public function testRunCommand(): void
    {
        $manager = new CommandsManager([], $this->loggerMock, $this->bufferedOutputMock);

        $commandMock = $this->createMock(Command::class);
        $commandMock
            ->expects(self::once())
            ->method('run')
        ;

        $this->bufferedOutputMock
            ->expects(self::once())
            ->method('fetch')
            ->willReturn('test')
        ;

        $outputMock = $this->createMock(OutputInterface::class);
        $outputMock
            ->expects(self::once())
            ->method('write')
            ->with('test')
        ;

        $this->loggerMock
            ->expects(self::once())
            ->method('debug');

        $manager->runCommand($commandMock, $outputMock);
    }


    public function testExecuteSlaveCommandWithoutApplication(): void
    {
        $manager = new CommandsManager([], $this->loggerMock, $this->bufferedOutputMock);
        $outputMock = $this->createMock(OutputInterface::class);
        $commandMock = $this->createMock(Command::class);
        $this->expectException(\LogicException::class);
        $manager->executeSlaveCommand($commandMock, $outputMock);
    }

    public function testExecuteSlaveCommand(): void
    {
        $manager = new CommandsManager([], $this->loggerMock, $this->bufferedOutputMock);
        $outputMock = $this->createMock(OutputInterface::class);
        $commandMock = $this->createMock(Command::class);
        $applicationMock = $this->createMock(Application::class);

        $commandMock
            ->expects(self::atLeast(1))
            ->method('getApplication')
            ->willReturn($applicationMock)
        ;
        $manager->executeSlaveCommand($commandMock, $outputMock);
    }

    public function testExecuteSlaveCommandChain(): void
    {
        $slaveCommand = new class extends Command implements ChainableInterface {
            public function __construct()
            {
                parent::__construct('slave');
            }

            public function getRootCommandName(): string
            {
                return 'root';
            }

            protected function execute(InputInterface $input, OutputInterface $output): int
            {
                $output->writeln('test');
                return Command::SUCCESS;
            }
        };
        $manager = new CommandsManager([$slaveCommand], $this->loggerMock, $this->bufferedOutputMock);
        $outputMock = $this->createMock(OutputInterface::class);
        $commandMock = $this->createMock(Command::class);
        $applicationMock = $this->createMock(Application::class);

        $commandMock
            ->expects(self::once(1))
            ->method('getApplication')
            ->willReturn($applicationMock)
        ;

        $commandMock
            ->expects(self::atLeast(1))
            ->method('getName')
            ->willReturn('root')
        ;

        $outputMock
            ->expects(self::once())
            ->method('write')
        ;

        $manager->executeSlaveCommand($commandMock, $outputMock);
    }

    public function testIsSlaveCommandReturnsTrue(): void
    {
        $commandInChain = $this->createMock(ChainableInterface::class);

        $commandInChain
            ->expects(self::atLeast(1))
            ->method('getName')
            ->willReturn('slave')
        ;

        $commandInChain
            ->expects(self::atLeast(1))
            ->method('getRootCommandName')
            ->willReturn('root')
        ;

        $checkedCommand = $this->createMock(Command::class);
        $checkedCommand
            ->expects(self::once())
            ->method('getName')
            ->willReturn('slave')
        ;

        $manager = new CommandsManager([$commandInChain], $this->createMock(LoggerInterface::class));
        $manager->isSlaveCommand($checkedCommand);
        $this->assertTrue(true);
    }
}
