<?php

namespace Tests\Resiliency\TransitionDispatchers;

use org\bovigo\vfs\vfsStream;
use PHPUnit\Framework\TestCase;
use Resiliency\Contracts\CircuitBreaker;
use Resiliency\TransitionDispatchers\SimpleDispatcher;

class SimpleDispatcherTest extends TestCase
{
    public function testCreation()
    {
        $this->assertInstanceOf(SimpleDispatcher::class, new SimpleDispatcher('php://stderr'));
    }

    public function testDispatch()
    {
        $root = vfsStream::setup();
        $file = vfsStream::newFile('logs.txt', 0644)
            ->withContent('')
            ->at($root)
        ;

        $circuitBreakerS = $this->createMock(CircuitBreaker::class);
        $circuitBreakerS->method('getState')
            ->willReturn('OPEN')
        ;

        $simpleDispatcher = new SimpleDispatcher($file->url());
        $simpleDispatcher->dispatch(
            $circuitBreakerS,
            'INIT',
            'http://test.org',
            [
                'a' => 1,
                'b' => 'B',
            ]
        );

        $expectedMessage = '[INIT]:"http://test.org"_(OPEN)_{"a":1,"b":"B"}';

        $this->assertSame($expectedMessage, $file->getContent());
    }
}
