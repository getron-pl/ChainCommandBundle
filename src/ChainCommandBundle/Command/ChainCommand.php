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

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;

/**
 * The base Command class for all commands that want to use Command Chain functionality.
 * It introduces methods that allows adding a current command to another command's command chain. 
 * It also adds utility methods that helps performing operations on commands chains.
 * 
 */
abstract class ChainCommand extends ContainerAwareCommand
{

    /**
     * Associative array of relations between commands. 
     *
     * @var array
     */
    static private $chainItems;

    /**
     *  {@inheritdoc}
     */
    public function __construct($name = null)
    {
        if (!is_array(self::$chainItems)) {
            self::$chainItems = [];
        }
        parent::__construct($name);
    }

    /**
     * Attaches current command to a command chain of $command
     * 
     * @param string $command command name
     * @return \ChainCommandBundle\Command\ChainCommand  Command The current instance
     */
    public function attachTo($command)
    {
        if (!isset(self::$chainItems[$command])) {
            self::$chainItems[$command] = [];
        }

        self::$chainItems[$command][] = $this;

        return $this;
    }

    /**
     * Returns an array that contains all commands attached to the chain.
     * 
     * @param string $command command name
     * @return array
     */
    public function getAttachedTo($command)
    {
        if (isset(self::$chainItems[$command])) {
            return self::$chainItems[$command];
        } else {
            return null;
        }
    }

    /**
     * Returns name of the command to whitch the current command is attached.
     * It returns only first occurence of attachment.
     * If no attachment detected - returns null
     * 
     * @return string
     */
    public function getFirstMasterName()
    {
        foreach (self::$chainItems as $masterCommandName => $masterCommand) {
            if (is_array($masterCommand)) {
                foreach ($masterCommand as $attachedCommand) {
                    if ($attachedCommand === $this) {
                        return $masterCommandName;
                    }
                }
            }
        }

        return null;
    }

}
