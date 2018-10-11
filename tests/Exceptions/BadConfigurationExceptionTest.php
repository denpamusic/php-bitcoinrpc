<?php

namespace Denpa\Bitcoin\Tests\Exceptions;

use Denpa\Bitcoin\Tests\TestCase;
use Denpa\Bitcoin\Exceptions\BadConfigurationException;

class BadConfigurationExceptionTest extends TestCase
{
    /**
     * Set-up test environment.
     *
     * @return void
     */
    protected function setUp()
    {
        parent::setUp();

        $this->config = ['test' => 'value'];
    }

    /**
     * Test trowing exception.
     *
     * @return void
     */
    public function testThrow()
    {
        $this->expectException(BadConfigurationException::class);
        $this->expectExceptionMessage('Test message');
        $this->expectExceptionCode(1);

        throw new BadConfigurationException($this->config, 'Test message', 1);
    }

    /**
     * Test config getter.
     *
     * @return void
     */
    public function testGetConfig()
    {
        $exception = new BadConfigurationException($this->config);

        $this->assertEquals($this->config, $exception->getConfig());
    }
}
