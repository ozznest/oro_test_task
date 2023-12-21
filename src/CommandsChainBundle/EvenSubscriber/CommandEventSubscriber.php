<?php

namespace App\CommandsChainBundle\EvenSubscriber;

use App\CommandsChainBundle\CommandsManager;
use App\CommandsChainBundle\RootCommandInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\ConsoleEvents;
use Symfony\Component\Console\Event\ConsoleCommandEvent;
use Symfony\Component\Console\Event\ConsoleTerminateEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

readonly class CommandEventSubscriber implements EventSubscriberInterface
{
    /**
     * @param Command[] $chainedServices
     */
    public function __construct(
        private iterable $chainedServices,
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
        foreach ($this->chainedServices as $command) {
            $this->logger->debug(sprintf(' %s registered as a member of %s command chain', $command->getName(), $command->getrootCommand()));
            if ($command->getName() === $event->getCommand()->getName()) {
                $event->disableCommand();
                $error = sprintf(
                    'Error: %s command is a member of %s command chain and cannot be executed on its own.',
                    $commandName,
                    $command->getRootCommand()
                );
                $event->getOutput()->writeln($error);
                $this->logger->error($error);
            }
        }
    }

    public function runRootCommand(ConsoleCommandEvent $event): void
    {
        $command = $event->getCommand();
        if ($command instanceof RootCommandInterface) {
            $this->logger->debug('Executing '.$command->getName().' command itself first:');
            $event->disableCommand();
            $this->commandsManager->runCommand($command, $event->getOutput());
        }
    }

    public function runChainCommandsForRoot(ConsoleTerminateEvent $event): void
    {
        $rootCommand = $event->getCommand();
        if ($rootCommand instanceof RootCommandInterface) {
            $log = sprintf('%s is a master command of a command chain that has registered member commands', $rootCommand->getName());
            if (count($this->chainedServices)) {
                $log .= 'that has registered member commands';
            }
            $this->logger->debug($log);
            $this->commandsManager->executeMembersCommand($event->getOutput(), $rootCommand);
        }
    }
}
