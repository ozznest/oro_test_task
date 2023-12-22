<?php

namespace App\Tests\Functional;

use App\ChainCommandBundle\Command\TestCommands\MasterCommand;
use App\ChainCommandBundle\Command\TestCommands\SlaveCommand;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\BufferedOutput;
use Symfony\Component\Console\Output\OutputInterface;

class ChainCommandTest extends KernelTestCase
{
    private Application $application;

    protected OutputInterface $bufferedOutput;

    private MasterCommand $masterCommand;

    private SlaveCommand $slaveCommand;


    protected function setUp(): void
    {
        parent::setUp();

        $kernel = self::bootKernel();
        $this->application = new Application($kernel);
        $this->application->setAutoExit(false);
        $this->bufferedOutput = new BufferedOutput();
        $this->masterCommand = new MasterCommand();
        $this->slaveCommand = new SlaveCommand();

        $this->application->add($this->masterCommand);
        $this->application->add($this->slaveCommand);
    }

    public function testChainCalledSuccessfully(): void
    {
        $this->application->run(
            new ArrayInput([$this->masterCommand->getName()]),
            $this->bufferedOutput
        );

        $this->assertSame("Hello from master!\nHi from Slave!\n", $this->bufferedOutput->fetch());
    }

    public function testChainCalledWithError(): void
    {
        $this->application->run(
            new ArrayInput([$this->slaveCommand->getName()]),
            $this->bufferedOutput
        );
        $expectedError = "Error: slave:command command is a member of master:command command chain and cannot be executed on its own.\n";
        $this->assertSame($expectedError, $this->bufferedOutput->fetch());
    }
}
