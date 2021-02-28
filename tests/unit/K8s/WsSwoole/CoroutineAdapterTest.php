<?php

/**
 * This file is part of the k8s/ws-swoole library.
 *
 * (c) Chad Sikorra <Chad.Sikorra@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace unit\K8s\WsSwoole;

use K8s\Core\Exception\WebsocketException;
use K8s\Core\Websocket\Contract\FrameHandlerInterface;
use K8s\Core\Websocket\Contract\WebsocketConnectionInterface;
use K8s\WsSwoole\ClientFactory;
use K8s\WsSwoole\CoroutineAdapter;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\UriInterface;
use Swoole\Coroutine\Http\Client;
use Swoole\WebSocket\Frame;

class CoroutineAdapterTest extends TestCase
{
    /**
     * @var ClientFactory|\Mockery\LegacyMockInterface|\Mockery\MockInterface
     */
    private $factory;

    /**
     * @var CoroutineAdapter
     */
    private $subject;

    public function setUp(): void
    {
        parent::setUp();
        $this->factory = \Mockery::spy(ClientFactory::class);
        $this->subject = new CoroutineAdapter(
            [],
            $this->factory
        );
    }

    public function testItConnects(): void
    {
        $uri = \Mockery::spy(UriInterface::class);
        $uri->shouldReceive([
            'getPath' => '/foo',
            'getQuery' => '?meh=yay',
        ]);

        $request = \Mockery::spy(RequestInterface::class);
        $payloadHandler = \Mockery::spy(FrameHandlerInterface::class);

        $request->shouldReceive('getUri')->andReturn($uri);

        $client = \Mockery::spy(Client::class);
        $this->factory->shouldReceive('makeCoroutineClient')
            ->andReturn($client);

        $client->shouldReceive('upgrade')
            ->andReturn(true);

        $frame1 = \Mockery::spy(Frame::class);
        $frame2 = \Mockery::spy(Frame::class);
        $frame1->opcode = 2;
        $frame1->data = 'foo';
        $frame2->opcode = 6;

        $client->shouldReceive('recv')
            ->andReturn($frame1, $frame2);

        $this->subject->connect($request, $payloadHandler);
        $payloadHandler->shouldHaveReceived(
            'onReceive',
            [\Mockery::type(\K8s\Core\Websocket\Frame::class), \Mockery::type(WebsocketConnectionInterface::class)]
        );
        $payloadHandler->shouldHaveReceived('onClose');
        $payloadHandler->shouldHaveReceived('onConnect', [\Mockery::type(WebsocketConnectionInterface::class)]);
    }

    public function testItThrowsWebsocketExceptionIfTheUpgradeFails()
    {
        $uri = \Mockery::spy(UriInterface::class);
        $uri->shouldReceive([
            'getPath' => '/foo',
            'getQuery' => '?meh=yay',
        ]);

        $request = \Mockery::spy(RequestInterface::class);
        $payloadHandler = \Mockery::spy(FrameHandlerInterface::class);

        $request->shouldReceive('getUri')->andReturn($uri);

        $client = \Mockery::spy(Client::class);
        $this->factory->shouldReceive('makeCoroutineClient')
            ->andReturn($client);

        $client->shouldReceive('upgrade')
            ->andReturnFalse();

        $this->expectException(WebsocketException::class);
        $this->subject->connect($request, $payloadHandler);
    }
}
