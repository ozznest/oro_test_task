<?php

namespace App\ChainCommandBundle;

use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\BufferedOutput;
use Symfony\Component\Console\Output\OutputInterface;

class CommandsManager
{
    private array $slaveCommands = [];

    /**
     * @param ChainableInterface[] $chainedServices
     */
    public function __construct(
        private readonly iterable $chainedServices,
        private readonly LoggerInterface $logger,
        private ?BufferedOutput $bufferedOutput = null
    ) {
        if (!$bufferedOutput) {
            $this->bufferedOutput = new BufferedOutput();
        }
        /* @var $slaveCommand ChainableInterface */
        foreach ($chainedServices as $slaveCommand) {
            $this->logger->debug(sprintf(' %s registered as a member of %s command chain', $slaveCommand->getName(), $slaveCommand->getRootCommandName()));
            $this->slaveCommands[$slaveCommand->getRootCommandName()] ??= [];
            $this->slaveCommands[$slaveCommand->getRootCommandName()][] = $slaveCommand;
        }
    }

    public function isRootCommand(Command $command): bool
    {
        return isset($this->slaveCommands[$command->getName()])
           && count($this->slaveCommands[$command->getName()]);
    }

    public function runCommand(Command $command, OutputInterface $output): void
    {
        $command->run(new ArrayInput([]), $this->bufferedOutput);
        $outputMessage = $this->bufferedOutput->fetch();
        $this->logger->debug($outputMessage);
        $output->write($outputMessage);
    }

    public function executeSlaveCommand(Command $rootCommand, OutputInterface $output): void
    {
        $application = $rootCommand->getApplication();
        if (null === $application) {
            $error = 'Failed to determine application for console command event';
            $this->logger->error($error);
            throw new \LogicException($error);
        }
        $chain = $this->getCommandsChainForRootCommand($rootCommand);
        $this->logger->debug(sprintf('Executing %s chain members:', $rootCommand->getName()));
        foreach ($chain as $chainItemCommand) {
            $this->logger->debug(sprintf('%s registered as a member of %s command chain', $chainItemCommand->getName(), $rootCommand->getName()));
            $this->runCommand($chainItemCommand, $output);
            $this->logger->debug(sprintf('Execution of %s chain completed.', $chainItemCommand->getName()));
        }
    }

    public function isSlaveCommand(Command $chainable): bool
    {
        foreach ($this->chainedServices as $command) {
            if ($command->getName() === $chainable->getName()) {
                return true;
            }
        }

        return false;
    }

    protected function getCommandsChainForRootCommand(Command $rootCommand): array
    {
        if (!isset($this->slaveCommands[$rootCommand->getName()])) {
            return [];
        }
        $log = sprintf('%s is a master command of a command chain that has registered member commands  that has registered member commands', $rootCommand->getName());
        $this->logger->debug($log);

        return $this->slaveCommands[$rootCommand->getName()];
    }
}
