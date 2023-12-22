<?php

namespace App\FooBundle\Command;

use App\ChainCommandBundle\RootCommandInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class FooCommand extends Command implements RootCommandInterface
{
    public const NAME = 'foo:command';

    public function __construct()
    {
        parent::__construct(static::NAME);
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $output->writeln('Hello from Foo!');

        return Command::SUCCESS;
    }
}
