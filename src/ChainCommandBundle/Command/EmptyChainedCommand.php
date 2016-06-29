<?php

/*
 * This file is part of the ChainCommandBundle
 *
 * (c) Przemyslaw Nogaj <pn@getron.pl>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ChainCommandBundle\Command;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Implements empty 
 */
class EmptyChainedCommand extends \ChainCommandBundle\Command\ChainCommand
{

    protected function configure()
    {
        $this
            ->setName('chained:command')
            ->setDescription('Implementation of command with command chaining options that executes no action.');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $output->write($this->getName() . ' executed', true);
    }

}
