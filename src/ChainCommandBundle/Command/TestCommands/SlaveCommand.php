<?php

namespace App\ChainCommandBundle\Command\TestCommands;

use App\ChainCommandBundle\ChainableInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class SlaveCommand extends Command implements ChainableInterface
{
    public const NAME = 'slave:command';

    public function __construct()
    {
        parent::__construct(static::NAME);
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $output->writeln('Hi from Slave!');

        return Command::SUCCESS;
    }

    public function getRootCommandName(): string
    {
        return MasterCommand::NAME;
    }
}
