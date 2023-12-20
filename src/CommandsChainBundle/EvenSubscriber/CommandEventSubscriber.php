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

readonly class CommandEventSubscriber implements EventSubscriberInterface
{
    /**
     * @param Command[] $chainedServices
     */
    public function __construct(
        private iterable        $chainedServices,
        private LoggerInterface $logger
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            ConsoleEvents::COMMAND   => [
                ['disableSlaveCommands'] ,
                ['runRootCommand']
            ],
            ConsoleEvents::TERMINATE => 'runChainCommandsForRoot'
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
            $this->logger->debug('Executing ' . $command->getName() . ' command itself first:');
            $event->disableCommand();
            $this->runCommand($command, $event->getOutput());
        }
    }

    public function runChainCommandsForRoot(ConsoleTerminateEvent $event): void
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

    protected function executeMembersCommand(ConsoleTerminateEvent $event, Command $rootCommand): void
    {
        $application = $rootCommand->getApplication();
        if (null === $application) {
            $this->logger->error('Failed to determine application for console command event');
            throw new LogicException('Failed to determine application for console command event');
        }
        $chain = $this->getCommandsChainForRootCommand($rootCommand);
        if (count($chain)) {
            $this->logger->debug(sprintf('Executing %s chain members:', $rootCommand->getName()));
            foreach ($chain as $chainItemCommand) {
                $logMessage = sprintf('%s registered as a member of %s command chain', $chainItemCommand->getName(), $rootCommand->getName());
                $this->logger->debug($logMessage);
                $this->runCommand($chainItemCommand, $event->getOutput());
            }
            $this->logger->debug(sprintf('Execution of %s chain completed.', $chainItemCommand->getName()));
        }
    }

    protected function runCommand(Command $command, OutputInterface $output): void
    {
        $bufferedOutput = new BufferedOutput();
        $command->run(new ArrayInput([]), $bufferedOutput);
        $outputMessage = $bufferedOutput->fetch();
        $this->logger->debug($outputMessage);
        //echo $outputMessage;
        $output->write($outputMessage);
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
}
