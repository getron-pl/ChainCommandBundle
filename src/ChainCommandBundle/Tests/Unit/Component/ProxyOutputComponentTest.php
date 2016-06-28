<?php

namespace ChainCommandBundle\Test;

use \ChainCommandBundle\Component\ProxyOutputComponent;

class ProxyOutputComponentTest extends \PHPUnit_Framework_TestCase
{

    private $proxy;

    protected function setUp()
    {
        $this->proxy = new \ChainCommandBundle\Component\ProxyOutputComponent();
    }

    public function testConstructor()
    {
        $this->assertInstanceOf('\ChainCommandBundle\Component\ProxyOutputComponent', $this->proxy);
    }

    /**
     * @expectedException Exception
     */
    public function testDoWriteWithoutLogger()
    {
        $this->proxy->write('test');
    }

}
