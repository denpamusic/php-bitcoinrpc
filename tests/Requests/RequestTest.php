<?php

namespace Denpa\Bitcoin\Tests\Request;

use Denpa\Bitcoin\Client as BitcoinClient;
use Denpa\Bitcoin\Requests\Request;
use Denpa\Bitcoin\Tests\TestCase;

class RequestTest extends TestCase
{
    /**
     * Set up test.
     *
     * @return void
     */
    public function setUp() : void
    {
        parent::setUp();

        $this->request = new Request('getblock', 'foo');
    }

    /**
     * Test request method getter.
     *
     * @return void
     */
    public function testGetMethod() : void
    {
        $this->assertEquals('getblock', $this->request->getMethod());
    }

    /**
     * Test request params getter.
     *
     * @return void
     */
    public function testGetParams() : void
    {
        $this->assertEquals(['foo'], $this->request->getParams());
    }

    /**
     * Test request params setter.
     *
     * @return void
     */
    public function testSetParams() : void
    {
        $this->request->setParams('bar', 'baz');

        $this->assertEquals(['bar', 'baz'], $this->request->getParams());
    }

    /**
     * Test request json serializer.
     *
     * @return void
     */
    public function testSerializeFor() : void
    {
        $array = $this->request->serializeFor(new BitcoinClient());

        $this->assertEquals([
            'method' => 'getblock',
            'params' => ['foo'],
            'id'     => 0,
        ], $array);
    }
}
