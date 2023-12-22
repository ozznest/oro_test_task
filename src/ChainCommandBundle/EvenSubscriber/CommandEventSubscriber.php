<?php

namespace App\ChainCommandBundle\EvenSubscriber;

use App\ChainCommandBundle\CommandsManager;
use App\ChainCommandBundle\RootCommandInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\ConsoleEvents;
use Symfony\Component\Console\Event\ConsoleCommandEvent;
use Symfony\Component\Console\Event\ConsoleTerminateEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

readonly class CommandEventSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private LoggerInterface $logger,
        private CommandsManager $commandsManager
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            ConsoleEvents::COMMAND => [
                ['disableSlaveCommands'],
                ['runRootCommand'],
            ],
            ConsoleEvents::TERMINATE => 'runChainCommandsForRoot',
        ];
    }

    public function disableSlaveCommands(ConsoleCommandEvent $event): void
    {
        $command = $event->getCommand();
        $commandName = $command->getName();
        if ($this->commandsManager->isSlaveCommand($command)) {
            $event->disableCommand();
            $error = sprintf(
                'Error: %s command is a member of %s command chain and cannot be executed on its own.',
                $commandName,
                $command->getRootCommandName()
            );
            $event->getOutput()->writeln($error);
            $this->logger->error($error);
        }
    }

    public function runRootCommand(ConsoleCommandEvent $event): void
    {
        $command = $event->getCommand();
        if ($command instanceof RootCommandInterface) {
            $this->logger->debug(sprintf('Executing %s command itself first:', $command->getName()));
            $event->disableCommand();
            $this->commandsManager->runCommand($command, $event->getOutput());
        }
    }

    /**
     * run root command for getting output into buffer.
     */
    public function runChainCommandsForRoot(ConsoleTerminateEvent $event): void
    {
        $command = $event->getCommand();
        if($this->commandsManager->isRootCommand($command)){
            $this->commandsManager->executeSlaveCommand($command, $event->getOutput());
        }
    }
}
