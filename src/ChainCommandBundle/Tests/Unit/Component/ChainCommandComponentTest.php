<?php

namespace ChainCommandBundle\Test;

use \ChainCommandBundle\Component\ChainCommandComponent;

class ChainCommandComponentTest extends \PHPUnit_Framework_TestCase
{

    private $chain;
    private $logger;
    private $proxy;
    private $command;

    protected function setUp()
    {
        $this->logger = $this->getMockBuilder('\Symfony\Bridge\Monolog\Logger')
            ->setConstructorArgs(['logger'])
            ->getMock();
        $this->proxy = new \ChainCommandBundle\Component\ProxyOutputComponent();
        $this->chain = new \ChainCommandBundle\Component\ChainCommandComponent($this->logger, $this->proxy);
        $this->command = new \ChainCommandBundle\Command\EmptyChainedCommand('test');
    }

    public function testConstructor()
    {
        $this->assertInstanceOf('\ChainCommandBundle\Component\ChainCommandComponent', $this->chain);
    }

    public function testBeforeCommandPassingListCommand()
    {
        $command = new \Symfony\Component\Console\Command\ListCommand('test:test');
        $input = new \Symfony\Component\Console\Input\ArrayInput([]);
        $output = new \Symfony\Component\Console\Output\BufferedOutput();
        $event = new \Symfony\Component\Console\Event\ConsoleCommandEvent($command, $input, $output);
        $this->chain->beforeCommand($event);

        $this->assertEmpty($output->fetch());
    }

    public function testBeforeCommandWitoutAttachedCommand()
    {
        $input = new \Symfony\Component\Console\Input\ArrayInput([]);
        $output = new \Symfony\Component\Console\Output\BufferedOutput();
        $event = new \Symfony\Component\Console\Event\ConsoleCommandEvent($this->command, $input, $output);
        $this->chain->beforeCommand($event);

        $this->assertContains('chained:command executed', $output->fetch());
    }

    public function testBeforeCommandWithAttachedCommand()
    {
        $command2 = new \ChainCommandBundle\Command\EmptyChainedCommand('test2');
        $command2->attachTo('chained:command');

        $input = new \Symfony\Component\Console\Input\ArrayInput([]);
        $output = new \Symfony\Component\Console\Output\BufferedOutput();
        $event = new \Symfony\Component\Console\Event\ConsoleCommandEvent($this->command, $input, $output);
        $this->chain->beforeCommand($event);

        $this->assertContains('chained:command executed', $output->fetch());
    }

    public function testBeforeCommandAsChained()
    {
        $this->command->attachTo('chained:command');

        $input = new \Symfony\Component\Console\Input\ArrayInput([]);
        $output = new \Symfony\Component\Console\Output\BufferedOutput();
        $event = new \Symfony\Component\Console\Event\ConsoleCommandEvent($this->command, $input, $output);
        $this->chain->beforeCommand($event);

        $this->assertContains('cannot be executed on its own', $output->fetch());
    }

    public function testAfterCommand()
    {
        $this->testBeforeCommandWithAttachedCommand();
        $input = new \Symfony\Component\Console\Input\ArrayInput([]);
        $output = new \Symfony\Component\Console\Output\BufferedOutput();
        $exitCode = 0;
        $event = new \Symfony\Component\Console\Event\ConsoleTerminateEvent($this->command, $input, $output, $exitCode);
        $this->chain->afterCommand($event);

        $this->assertContains('chained:command executed', $output->fetch());
    }

    public function testGetFirstMasterName()
    {
        $expected = 'chained:command';
        $this->command->attachTo($expected);
        $actual = $this->command->getFirstMasterName();

        $this->assertEquals($expected, $actual);
    }

}
