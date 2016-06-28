<?php

/*
 * This file is part of the ChainCommandBundle
 *
 * (c) Przemyslaw Nogaj <pn@getron.pl>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ChainCommandBundle\Component;

use Symfony\Component\Console\Output\OutputInterface;

/**
 * Proxy class for command output.
 * It logs command results and passes control to oryginal output object .
 *
 */
class ProxyOutputComponent extends \Symfony\Component\Console\Output\ConsoleOutput implements OutputInterface
{

    const ERR_NOOUTPUASSIGNED = 'Trying to use ProxyOutput with no base output object assigned.';

    private $output;

    /**
     * Logger object
     *
     * @var \Symfony\Bridge\Monolog\Logger
     */
    private $logger;

    /**
     * Assigns original output obiect
     * 
     * @param OutputInterface $output Original output obiect
     */
    public function setOutput(OutputInterface $output)
    {
        $this->output = $output;
    }

    /**
     * Assigns logger obiect 
     * 
     * @param \Symfony\Bridge\Monolog\Logger $logger Logger obiect
     */
    public function setLogger(\Symfony\Bridge\Monolog\Logger $logger)
    {
        $this->logger = $logger;
    }

    /**
     * {@inheritdoc} In addition, logs message passed to the method to using logger, if assigned.
     */
    protected function doWrite($message, $newline)
    {
        if (is_object($this->logger)) {
            $this->logger->info($message);
        }

        if (is_object($this->output)) {
            $this->output->doWrite($message, $newline);
        } else {
            throw new \LogicException(self::ERR_NOOUTPUASSIGNED);
        }
    }

}
