<?php

namespace App\CommandsChainBundle\EvenSubscriber;

use App\CommandsChainBundle\ChainableInterface;
use App\CommandsChainBundle\RootCommandInterface;
use LogicException;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\ConsoleEvents;
use Symfony\Component\Console\Event\ConsoleCommandEvent;
use Symfony\Component\Console\Event\ConsoleTerminateEvent;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\BufferedOutput;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class CommandEventSubscriber implements EventSubscriberInterface
{
    /**
     * @param Command[] $chainedServices
     */
    public function __construct(
        private readonly iterable $chainedServices,
        private readonly LoggerInterface $logger
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            ConsoleEvents::COMMAND   => [
                ['disableTaggedCommands'] ,
                ['runRootCommand']
            ],
            ConsoleEvents::TERMINATE => 'afterCommand'
        ];
    }

    public function disableTaggedCommands(ConsoleCommandEvent $event): void
    {
        $command = $event->getCommand();
        $commandName = $command->getName();
        foreach ($this->chainedServices as $command) {
            $this->logger->debug($command->getName() .  ' registered as a member of foo:hello command chain');
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
            $this->logger->debug('Executing ' . $command->getName() . ' command itself first:');
            $event->disableCommand();
            $bufferedOutput = $this->getBufferedOutput();
            $command->run(new ArrayInput([]), $bufferedOutput);
            $outputMessage = $bufferedOutput->fetch();
            $this->logger->debug($outputMessage);
            $event->getOutput()->write($outputMessage);
        }
    }

    public function afterCommand(ConsoleTerminateEvent $event): void
    {
        $command = $event->getCommand();
        if ($command instanceof RootCommandInterface) {
            $log = sprintf('%s is a master command of a command chain that has registered member commands', $command->getName());
            if(count($this->chainedServices)) {
                $log .= 'that has registered member commands';
            }
            $this->logger->debug($log);
            $this->executeMembersCommand($event, $command);
        }
    }

    protected function executeMembersCommand(ConsoleTerminateEvent $event, Command $command): void
    {
        $application = $command->getApplication();
        if (null === $application) {
            $this->logger->error('Failed to determine application for console command event');
            throw new LogicException('Failed to determine application for console command event');
        }
        $chain = $this->getCommandsChainForRootCommand($command);
        if (count($chain)) {
            $this->logger->debug('Executing foo:hello chain members:');
            foreach ($chain as $command) {
                $logMessage = $command->getName() . ' registered as a member of foo:hello command chain';
                $this->logger->debug($logMessage);
                $bufferedOutput = $this->getBufferedOutput();
                $command->run(new ArrayInput([]), $bufferedOutput);
                $outputMessage = $bufferedOutput->fetch();
                $this->logger->debug($outputMessage);
                $event->getOutput()->write($outputMessage);
            }
            $this->logger->debug('Execution of foo:hello chain completed.');
        }
    }

    protected function getCommandsChainForRootCommand(Command $rootCommand): array
    {
        $chain = [];
        /* @var $service ChainableInterface */
        foreach ($this->chainedServices as $service) {
            if($service->getRootCommand() === $rootCommand->getName()) {
                $chain[] = $service;
            }
        }
        return $chain;
    }

    protected function getBufferedOutput(): OutputInterface
    {
        return new BufferedOutput();
    }
}
