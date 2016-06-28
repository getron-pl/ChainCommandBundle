<?php

/*
 * This file is part of the ChainCommandBundle
 *
 * (c) Przemyslaw Nogaj <pn@getron.pl>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ChainCommandBundle;

use Symfony\Component\HttpKernel\Bundle\Bundle;
use Symfony\Component\Console\Event\ConsoleCommandEvent;
use Symfony\Component\Console\Event\ConsoleTerminateEvent;
use Symfony\Component\Console\ConsoleEvents;

/**
 * The main bundle class that set ups listeners and the a Command Chaining engine.
 * 
 */
class ChainCommandBundle extends Bundle
{

    /**
     * A Command Chaining engine
     * @var Component\ChainCommandComponent
     */
    private $chain;

    /**
     * Extending Console Command compoment by adding listeneres
     * 
     */
    public function boot()
    {
        $this->chain = $this->container->get('chain_command');
        $dispatcher = $this->container->get('event_dispatcher');
        $dispatcher->addListener(ConsoleEvents::COMMAND, array($this, 'onCommand'));
        $dispatcher->addListener(ConsoleEvents::TERMINATE, array($this, 'onTerminate'));

        parent::boot();
    }

    /**
     * Delegates control to ChainCommandComponent
     * 
     * @param ConsoleCommandEvent $event
     */
    public function onCommand(ConsoleCommandEvent $event)
    {
        $this->chain->beforeCommand($event);
    }

    /**
     * Delegates control to ChainCommandComponent
     * 
     * @param ConsoleTerminateEvent $event
     */
    public function onTerminate(ConsoleTerminateEvent $event)
    {
        $this->chain->afterCommand($event);
    }

    /**
     * Removing Console Command componens listeners
     */
    public function shutdown()
    {
        $dispatcher = $this->container->get('event_dispatcher');
        $dispatcher->removeListener(ConsoleEvents::COMMAND, array($this, 'onCommand'));
        $dispatcher->removeListener(ConsoleEvents::TERMINATE, array($this, 'onTerminate'));
        parent::shutdown();
    }

    /**
     * Returns chain objects
     * 
     * @return Component\ChainCommandComponent
     */
    public function getChain(){
       return $this->chain; 
    }
}
