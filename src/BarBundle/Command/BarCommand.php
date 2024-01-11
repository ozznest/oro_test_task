<?php

namespace App\BarBundle\Command;

use App\ChainCommandBundle\ChainableInterface;
use App\FooBundle\Command\FooCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class BarCommand extends Command implements ChainableInterface
{
    public const NAME = 'bar:hi';

    public function __construct()
    {
        parent::__construct(static::NAME);
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $output->writeln('Hi from Bar!');

        return Command::SUCCESS;
    }

    public function getRootCommandName(): string
    {
        return FooCommand::NAME;
    }
}
