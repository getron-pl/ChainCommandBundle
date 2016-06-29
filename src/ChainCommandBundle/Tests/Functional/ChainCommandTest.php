<?php

namespace ChainCommandBundle\Tests\Functional;

use ChainCommandBundle\Tests\Functional\CommandTestCase;

class ChainCommandTest extends CommandTestCase
{

    public function testIfInstalled()
    {
        $client = self::createClient();
        $output = $this->runCommand($client, "chained:command");
        $this->assertContains("chained:command executed", $output);
    }

    public function testIfFooHelloIsEnabledAndChained()
    {
        $client = self::createClient();
        $output = $this->runCommand($client, "foo:hello");
        $this->assertContains('Hello from Foo!', $output);
        $this->assertContains('Hi from Bar!', $output);
    }

    public function testIfBarHiIsDisabled()
    {
        $client = self::createClient();
        $output = $this->runCommand($client, "bar:hi");
        $this->assertContains('cannot be executed on its own', $output);
    }

}
