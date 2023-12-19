<?php

namespace App\FooBundle\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class FooCommand extends Command
{
    public function __construct()
    {
        parent::__construct('foo:command');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $output->writeln('foo:command');
        return Command::SUCCESS;
    }
}