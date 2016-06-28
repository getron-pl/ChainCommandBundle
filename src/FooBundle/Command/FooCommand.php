<?php

/*
 * This file is part of the ChainCommandBundle
 *
 * (c) Przemyslaw Nogaj <pn@getron.pl>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace FooBundle\Command;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class FooCommand extends \ChainCommandBundle\Command\ChainCommand
{

    protected function configure()
    {
        $this
            ->setName('foo:hello')
            ->setDescription('Command to test ChainCommandBundle');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $output->writeln('Hello from Foo!');
    }

}
