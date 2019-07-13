<?php

namespace Tests\Resiliency\TransitionDispatchers;

use org\bovigo\vfs\vfsStream;
use Resiliency\Contracts\CircuitBreaker;
use Resiliency\Places\OpenPlace;
use Resiliency\TransitionDispatchers\SimpleDispatcher;
use Tests\Resiliency\CircuitBreakerTestCase;

class SimpleDispatcherTest extends CircuitBreakerTestCase
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

        $openPlace = new OpenPlace(1.0);
        $circuitBreakerS = $this->createMock(CircuitBreaker::class);
        $circuitBreakerS->method('getState')
            ->willReturn($openPlace)
        ;

        $simpleDispatcher = new SimpleDispatcher($file->url());
        $simpleDispatcher->dispatch(
            $circuitBreakerS,
            $this->getService('http://test.org', [
                'a' => 1,
                'b' => 'B',
            ]),
            'INIT'
        );

        $expectedMessage = '[INIT]:"http://test.org"_(OPEN)_{"a":1,"b":"B"}';

        $this->assertSame($expectedMessage, $file->getContent());
    }
}
