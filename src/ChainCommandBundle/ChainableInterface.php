<?php

namespace App\ChainCommandBundle;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\DependencyInjection\Attribute\Autoconfigure;

#[Autoconfigure(tags: ['app.chained.console'])]
interface ChainableInterface
{
    public function getRootCommandName(): string;

    public function run(InputInterface $input, OutputInterface $output): int;

    public function getName(): ?string;
}
