<?php

namespace Fjor;

class PluginTest extends \PHPUnit_Framework_TestCase
{
    public function setup()
    {
        $this->ioc = \Fjor\FjorFactory::create();
    }

    /**
     * @test
     */
    public function pluginsAreAllowedToInteractWithFjor()
    {
        $plugin = new \Fjor\AfterCreatePlugin();

        $this->ioc->addPlugin($plugin);

        $this->ioc->get('stdClass');
        $this->assertFalse($plugin->wasCalledAfterCreationOf('SplObjectStorage'));

        $this->ioc->get('SplObjectStorage');
        $this->assertTrue($plugin->wasCalledAfterCreationOf('SplObjectStorage'));
    }
}
