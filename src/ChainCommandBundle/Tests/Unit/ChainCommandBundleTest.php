<?php

namespace ChainCommandBundle\Test;

use \ChainCommandBundle\ChainCommandBundle;

class ChainCommandBundleTest extends \PHPUnit_Framework_TestCase
{

    private $bundle;
    private $dispatcher;
    private $logger;
    private $chain;
    private $container;

    protected function setUp()
    {
        $this->logger = $this->getMockBuilder('\Symfony\Bridge\Monolog\Logger')
            ->setConstructorArgs(['logger'])
            ->getMock();
        $this->bundle = new ChainCommandBundle();
        $this->dispatcher = new \Symfony\Component\EventDispatcher\EventDispatcher();
        $this->proxy = new \ChainCommandBundle\Component\ProxyOutputComponent();
        $this->chain = new \ChainCommandBundle\Component\ChainCommandComponent($this->logger, $this->proxy);
        $this->container = new \Symfony\Component\DependencyInjection\Container();
        $this->bundle->setContainer($this->container);
    }

    public function testConstructor()
    {
        $this->assertInstanceOf('\ChainCommandBundle\ChainCommandBundle', $this->bundle);
    }

    /**
     * @expectedException Exception
     */
    public function testBootWithoutChainCommandServiceRegistered()
    {
        $this->container->set('event_dispatcher', $this->dispatcher);

        $this->bundle->boot();
    }

    public function testBootWitchChainCommandServiceRegistered()
    {
        $this->container->set('event_dispatcher', $this->dispatcher);
        $this->container->set('chain_command', $this->chain);

        $this->bundle->boot();
    }

    public function testGetChain()
    {
        $this->testBootWitchChainCommandServiceRegistered();
        $chain = $this->bundle->getChain();
        $this->assertInstanceOf('\ChainCommandBundle\Component\ChainCommandComponent', $chain);
    }

    public function testOnCommand()
    {
        $this->testBootWitchChainCommandServiceRegistered();

        $command = new \ChainCommandBundle\Command\EmptyChainedCommand();
        $input = new \Symfony\Component\Console\Input\ArrayInput([]);
        $output = new \Symfony\Component\Console\Output\BufferedOutput();
        $event = new \Symfony\Component\Console\Event\ConsoleCommandEvent($command, $input, $output);

        $this->bundle->onCommand($event);
        $this->assertContains('chained:command executed', $output->fetch());
    }

    public function testOnTerminate()
    {
        $this->testBootWitchChainCommandServiceRegistered();

        $command = new \ChainCommandBundle\Command\EmptyChainedCommand();
        $input = new \Symfony\Component\Console\Input\ArrayInput([]);
        $output = new \Symfony\Component\Console\Output\BufferedOutput();
        $exitCode = 0;
        $event = new \Symfony\Component\Console\Event\ConsoleTerminateEvent($command, $input, $output, $exitCode);
        $this->bundle->onTerminate($event);

        $this->assertEmpty($output->fetch());
    }

    public function testShutdown()
    {
        $this->testBootWitchChainCommandServiceRegistered();
        $this->bundle->shutdown();
    }

}
