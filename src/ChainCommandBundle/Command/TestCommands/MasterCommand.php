<?php

namespace App\ChainCommandBundle\Command\TestCommands;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class MasterCommand extends Command
{
    public const NAME = 'master:command';

    public function __construct()
    {
        parent::__construct(static::NAME);
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $output->writeln('Hello from master!');

        return Command::SUCCESS;
    }
}
