<?php

namespace App\BarBundle\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class BarCommand extends Command
{
    public function __construct()
    {
        parent::__construct('bar:command');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $output->writeln('bar:command');
        return Command::SUCCESS;
    }
}