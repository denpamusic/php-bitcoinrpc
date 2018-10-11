<?php

namespace Denpa\Bitcoin\Tests\Exceptions;

use GuzzleHttp\Psr7\Request;
use Denpa\Bitcoin\Tests\TestCase;
use Denpa\Bitcoin\Exceptions\ConnectionException;

class ConnectionExceptionTest extends TestCase
{
    /**
     * Set-up test environment.
     *
     * @return void
     */
    protected function setUp()
    {
        parent::setUp();

        $this->request = $this
            ->getMockBuilder(Request::class)
            ->disableOriginalConstructor()
            ->getMock();
    }

    /**
     * Test trowing exception.
     *
     * @return void
     */
    public function testThrow()
    {
        $this->expectException(ConnectionException::class);
        $this->expectExceptionMessage('Test message');
        $this->expectExceptionCode(1);

        throw new ConnectionException($this->request, 'Test message', 1);
    }

    /**
     * Test request getter.
     *
     * @return void
     */
    public function testGetRequest()
    {
        $exception = new ConnectionException($this->request);

        $this->assertInstanceOf(Request::class, $exception->getRequest());
    }

    /**
     * Test constructor parameters getter.
     *
     * @return void
     */
    public function testGetConstructionParameters()
    {
        $exception = new FakeConnectionException($this->request);

        $this->assertEquals(
            [
                $exception->getRequest(),
                $exception->getMessage(),
                $exception->getCode(),
            ],
            $exception->getConstructorParameters()
        );
    }
}

class FakeConnectionException extends ConnectionException
{
    public function getConstructorParameters()
    {
        return parent::getConstructorParameters();
    }
}