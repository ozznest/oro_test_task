<?php

namespace App\Tests\CommandsChainBundle\EventSubscriber;

use App\CommandsChainBundle\EvenSubscriber\CommandEventSubscriber;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Event\ConsoleCommandEvent;
use Symfony\Component\Console\Event\ConsoleTerminateEvent;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class CommandEventSubscriberTest extends TestCase
{
    public function testAfterCommand(): void
    {

        $command = $this->createMock(Command::class);
        $input = $this->createMock(InputInterface::class);
        $output = $this->createMock(OutputInterface::class);
        $commandService = $this->createMock(Command::class);
        $consoleEvent = new ConsoleTerminateEvent($command, $input, $output);
        $subscriber = new CommandEventSubscriber([$commandService]);
        $subscriber->afterCommand($consoleEvent);
    }
}