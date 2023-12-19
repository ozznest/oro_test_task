<?php

namespace App\CommandsChainBundle\EvenSubscriber;

use Symfony\Component\Console\ConsoleEvents;
use Symfony\Component\Console\Event\ConsoleCommandEvent;
use Symfony\Component\Console\Event\ConsoleTerminateEvent;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\BufferedOutput;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Console\Command\Command;

class CommandEventSubscriber implements EventSubscriberInterface
{
    public function __construct(private iterable $chainedServices
    ) {
    }

    public static function getSubscribedEvents()
    {
        return [
            ConsoleEvents::COMMAND => 'disableTaggedCommands',
            ConsoleEvents::TERMINATE => 'afterCommand'
        ];
    }

    public function disableTaggedCommands(ConsoleCommandEvent $event): void
    {
        $command = $event->getCommand();
        $commandName = $command->getName();
        foreach ($this->chainedServices as $command) {
            if ($command->getName() === $event->getCommand()->getName()) {
                $event->disableCommand();
                $event->getOutput()->writeln(sprintf(
                    'Error: %s command is a member of %s command chain and cannot be executed on its own.',
                    $commandName, 'foo:command'
                ));
            }
        }
    }

    public function afterCommand(ConsoleTerminateEvent $event): void
    {
        $command = $event->getCommand();
        $this->executeMembersCommand($event, $command);
    }

    protected function executeMembersCommand(ConsoleTerminateEvent $event, Command $command): void
    {
        $application = $command->getApplication();
        if (null === $application) {
            throw new \LogicException('Failed to determine application for console command event');
        }
        foreach ($this->chainedServices as $command){
            $bufferedOutput = $this->getBufferedOutput();
            $command->run(new ArrayInput([]), $bufferedOutput);
            $outputMessage = $bufferedOutput->fetch();
            $event->getOutput()->write($outputMessage);
        }

    }

    protected function getBufferedOutput(): OutputInterface
    {
        return new BufferedOutput();
    }
}