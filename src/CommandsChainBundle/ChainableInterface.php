<?php

namespace App\CommandsChainBundle;

use Symfony\Component\DependencyInjection\Attribute\Autoconfigure;
#[Autoconfigure(tags: ['app.chained.console'])]
interface ChainableInterface
{

}