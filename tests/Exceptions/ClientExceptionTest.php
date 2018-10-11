<?php

namespace Denpa\Bitcoin\Tests\Exceptions;

use Denpa\Bitcoin\Tests\TestCase;
use Denpa\Bitcoin\Responses\Response;
use Denpa\Bitcoin\Exceptions\ClientException;

class ClientExceptionTest extends TestCase
{
    /**
     * Test exception namespace setter.
     *
     * @return void
     */
    public function testWithNamespace()
    {
        $exception = (new FakeClientException())
            ->withNamespace('Test\\Exceptions');

        $this->assertInstanceOf(
            \Test\Exceptions\FakeClientException::class,
            $exception
        );
    }

    /**
     * Test namespace setter with nonexistent namespace.
     *
     * @return void
     */
    public function testWithNamespaceWithNonexistentClass()
    {
        $exception = (new FakeClientException())
            ->withNamespace('Test\\Nonexistents');

        $this->assertInstanceOf(FakeClientException::class, $exception);
    }

    /**
     * Test exception class name getter.
     *
     * @return void
     */
    public function testGetClassName()
    {
        $exception = new FakeClientException;

        $this->assertEquals($exception->getClassName(), 'FakeClientException');
    }
}

class FakeClientException extends ClientException
{
    // original ClientException is an abstract class

    public function getClassName()
    {
        return parent::getClassName();
    }

    protected function getConstructorParameters()
    {
        return [];
    }
}

namespace Test\Exceptions;

class FakeClientException extends \Denpa\Bitcoin\Tests\Exceptions\FakeClientException
{
    // same as above in different namespace
}
