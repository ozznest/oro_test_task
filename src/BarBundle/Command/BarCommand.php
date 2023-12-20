<?php

namespace App\BarBundle\Command;

use App\CommandsChainBundle\ChainableInterface;
use App\FooBundle\Command\FooCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class BarCommand extends Command implements ChainableInterface
{
    public const NAME = 'bar:command';

    public function __construct()
    {
        parent::__construct(static::NAME);
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $output->writeln(static::NAME);
        return Command::SUCCESS;
    }

    public function getRootCommand(): string
    {
       return FooCommand::NAME;
    }
}