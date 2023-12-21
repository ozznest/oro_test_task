<?php

namespace App\CommandsChainBundle;

use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\BufferedOutput;
use Symfony\Component\Console\Output\OutputInterface;

class CommandsManager
{
    public function __construct(
        private readonly iterable $chainedServices,
        private LoggerInterface $logger,
        private ?BufferedOutput $bufferedOutput = null
    ) {
        if (!$bufferedOutput) {
            $this->bufferedOutput = new BufferedOutput();
        }
    }

    public function runCommand(Command $command, OutputInterface $output): void
    {
        $command->run(new ArrayInput([]), $this->bufferedOutput);
        $outputMessage = $this->bufferedOutput->fetch();
        $this->logger->debug($outputMessage);
        $output->write($outputMessage);
    }

    public function executeMembersCommand(OutputInterface $output, Command $rootCommand): void
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

    protected function getCommandsChainForRootCommand(Command $rootCommand): array
    {
        $chain = [];
        /* @var $service ChainableInterface */
        foreach ($this->chainedServices as $service) {
            if ($service->getRootCommand() === $rootCommand->getName()) {
                $chain[] = $service;
            }
        }

        return $chain;
    }
}
