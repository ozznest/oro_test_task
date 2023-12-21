<?php

namespace App\CommandsChainBundle;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\DependencyInjection\Attribute\Autoconfigure;

#[Autoconfigure(tags: ['app.chained.console'])]
interface ChainableInterface
{
    public function getRootCommand(): string;

    public function run(InputInterface $input, OutputInterface $output): int;

    public function getName(): ?string;
}
