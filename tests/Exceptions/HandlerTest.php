<?php

namespace Denpa\Bitcoin\Tests\Exceptions;

use Denpa\Bitcoin\Exceptions;
use Denpa\Bitcoin\Exceptions\Handler as ExceptionHandler;
use Denpa\Bitcoin\Tests\TestCase;
use Exception;

class HandlerTest extends TestCase
{
    /**
     * Cleans-up test environment.
     *
     * @return void
     */
    protected function tearDown()
    {
        parent::tearDown();

        // Remove all added handlers.
        ExceptionHandler::clearInstance();
    }

    /**
     * Test singleton instantiation.
     *
     * @return void
     */
    public function testSingleton()
    {
        $this->assertInstanceOf(
            ExceptionHandler::class,
            ExceptionHandler::getInstance()
        );
    }

    /**
     * Test handler registration.
     *
     * @return void
     */
    public function testRegisterHandler()
    {
        ExceptionHandler::getInstance()->registerHandler(function ($exception) {
            $this->assertEquals('Test message', $exception->getMessage());
        });

        $this->expectException(Exception::class);

        ExceptionHandler::getInstance()->handle(new Exception('Test message'));
    }

    /**
     * Test exception namespace setter.
     *
     * @return void
     */
    public function testSetNamespace()
    {
        $this->expectException(BadConfigurationException::class);
        $this->expectExceptionMessage('Test message');

        ExceptionHandler::getInstance()->setNamespace('Denpa\\Bitcoin\\Tests\\Exceptions');
        ExceptionHandler::getInstance()->handle(
            new Exceptions\BadConfigurationException(['foo' => 'bar'], 'Test message')
        );
    }
}

class BadConfigurationException extends Exceptions\BadConfigurationException
{
    //
}
