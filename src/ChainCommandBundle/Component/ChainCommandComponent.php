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

use Symfony\Component\Console\Event\ConsoleCommandEvent;
use Symfony\Component\Console\Event\ConsoleTerminateEvent;

/**
 * Class defines the Chaining Commands Engine
 * 
 * The class handles ConsoleEvents::COMMAND event to check if current command can be run on its own 
 * (i.e. not exists in another command's chain) and run the command if possible. It enables or denied 
 * running commands in its command's chain at the same time.
 * 
 * It also handles ConsoleEvents::TERMINATE event to run commands in current command's chain eventually.
 * 
 */
class ChainCommandComponent
{

    const ERROR_COMMANDINCHAIN = 'Error: %s command is a member of %s command chain and cannot be executed on its own.';
    const MSG_MASTEREXECUTION = '%s is a master command of a command chain that has registered member commands';
    const MSG_SLAVEATTACHED = '%s registered as a member of %s command chain';
    const MSG_MASTERITSELF = 'Executing %s command itself first:';
    const MSG_COMPLETED = 'Execution of %s chain completed.';
    const MSG_CHAINCOMMAND = 'Executing %s chain members:';

    /**
     * Instance of a logger 
     *
     * @var \Symfony\Bridge\Monolog\Logger
     */
    private $logger;

    /**
     * The flag that informs if we can run chained commands
     * 
     * @var bool
     */
    private $process;

    /**
     * Proxy output ConsoleOutput to enable logging output of the command
     * 
     * @var \ProxyOutputComponent
     * @see \Symfony\Component\Console\Output\ConsoleOutput
     */
    private $proxy;

    /**
     * Assing the logger and proxy instances
     * 
     * @param \Symfony\Bridge\Monolog\Logger $logger
     * @param \ChainCommandBundle\Component\ProxyOutputComponent $proxy
     */
    public function __construct(\Symfony\Bridge\Monolog\Logger $logger, \ChainCommandBundle\Component\ProxyOutputComponent $proxy)
    {
        $this->logger = $logger;

        $this->proxy = $proxy;
        $this->proxy->setLogger($this->logger);
    }

    /**
     * The method called before command execution. It checks if a command 
     * is in the chain of another command and if yes, it denied execution. 
     * If current command doesn't exists in the a chain of another command,
     * the method performs it.
     * 
     * @param ConsoleCommandEvent $event
     */
    public function beforeCommand(ConsoleCommandEvent &$event)
    {
        if (get_class($event->getCommand()) === "Symfony\Component\Console\Command\ListCommand") {
            return;
        }

        $command = $event->getCommand();
        if (method_exists($command, 'getFirstMasterName')) {
            $event->disableCommand();
            $output = $event->getOutput();
            $commandName = $command->getName();

            // get a name of a first command that contains current command in its chain
            $master = $command->getFirstMasterName();

            if (!is_null($master)) {
                $output->writeln(sprintf(self::ERROR_COMMANDINCHAIN, $commandName, $master));
                $this->process = 0;
            } else {
                $this->logger->info(sprintf(self::MSG_MASTEREXECUTION, $commandName));
                $this->logChainMembers($command);
                $this->logger->info(sprintf(self::MSG_MASTERITSELF, $commandName));
                $this->proxy->setOutput($output);
                $command->run($event->getInput(), $this->proxy);
                $this->process = 1;
            }
        }
    }

    /**
     * 
     */
    private function logChainMembers($command)
    {
        $commandName = $command->getName();
        $attachedCommands = $command->getAttachedTo($commandName);

        if (is_array($attachedCommands)) {
            foreach ($attachedCommands as $attachedCommand) {
                $this->logger->info(sprintf(self::MSG_SLAVEATTACHED, $attachedCommand->getName(), $commandName));
            }
        }
    }

    /**
     * Event executed after the command processing. Invokes chain processing 
     * and logs the completeness of the process.
     * 
     * @param ConsoleTerminateEvent $event
     */
    public function afterCommand(ConsoleTerminateEvent &$event)
    {
        if ($this->process) {
            $this->executeChainElements($event);
            $this->logger->info(sprintf(self::MSG_COMPLETED, $event->getCommand()->getName()));
        }
    }

    /**
     * Executes commands in a chain attached to the current command.
     * 
     * @param ConsoleTerminateEvent $event
     */
    public function executeChainElements(ConsoleTerminateEvent $event)
    {
        $command = $event->getCommand();
        $commandName = $command->getName();
        $attachedCommands = $command->getAttachedTo($commandName);

        if (is_array($attachedCommands)) {
            $input = $event->getInput();
            $this->proxy->setOutput($event->getOutput());
            $this->logger->info(sprintf(self::MSG_CHAINCOMMAND, $commandName));

            foreach ($attachedCommands as $attachedCommand) {
                $attachedCommand->run($input, $this->proxy);
            }
        }
    }

}
