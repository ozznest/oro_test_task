<?php

namespace FooBundle\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class TestCommand extends Command
{
    public function __construct()
    {
        parent::__construct('foo:test');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $output->writeln('test');
        return Command::SUCCESS;
    }
}