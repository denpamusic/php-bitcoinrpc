<?php

namespace Denpa\Bitcoin\Tests;

use Denpa\Bitcoin\Config;

class ConfigTest extends TestCase
{
    /**
     * Set up test.
     *
     * @return void
     */
    public function setUp()
    {
        parent::setUp();

        $this->config = new Config([
            'user'     => 'testuser',
            'password' => 'testpass',
            'ca'       => __FILE__,
        ]);
    }

    /**
     * Test CA file getter.
     *
     * @return void
     */
    public function testGetCa()
    {
        $this->assertEquals(__FILE__, $this->config->getCa());
    }

    /**
     * Test authentication array getter.
     *
     * @return void
     */
    public function testGetAuth()
    {
        $this->assertEquals(['testuser', 'testpass'], $this->config->getAuth());
    }

    /**
     * Test dsn getter.
     *
     * @return void
     */
    public function testGetDsn()
    {
        $this->assertEquals('http://127.0.0.1:8332', $this->config->getDsn());
    }

    /**
     * Test config setter.
     *
     * @return void
     */
    public function testSet()
    {
        $this->config->set(['password' => 'testpass2']);

        $this->assertEquals('testpass2', $this->config->get('password'));
    }
}