<?php

namespace Denpa\Bitcoin\Tests\Exceptions;

use Denpa\Bitcoin\Exceptions\ClientException;
use Denpa\Bitcoin\Tests\TestCase;

class ClientExceptionTest extends TestCase
{
    /**
     * Test exception namespace setter.
     *
     * @return void
     */
    public function testWithNamespace(): void
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
    public function testWithNamespaceWithNonexistentClass(): void
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
    public function testGetClassName(): void
    {
        $exception = new FakeClientException();

        $this->assertEquals($exception->getClassName(), 'FakeClientException');
    }
}

class FakeClientException extends ClientException
{
    // original ClientException is an abstract class

    public function getClassName(): string
    {
        return parent::getClassName();
    }

    protected function getConstructorParameters(): array
    {
        return [];
    }
}

namespace Test\Exceptions;

class FakeClientException extends \Denpa\Bitcoin\Tests\Exceptions\FakeClientException
{
    // same as above in different namespace
}
