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

use K8s\WsSwoole\ClientFactory;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\UriInterface;
use Swoole\Coroutine\Http\Client;

class ClientFactoryTest extends TestCase
{
    /**
     * @var ClientFactory
     */
    private $subject;

    public function setUp(): void
    {
        parent::setUp();
        $this->subject = new ClientFactory();
    }

    public function testItCanMakeCoroutineClient(): void
    {
        $uri = \Mockery::spy(UriInterface::class);
        $uri->shouldReceive([
            'getHost' => 'foo.bar',
            'getPort' => 8443,
            'getScheme' => 'wss',
        ]);

        $request = \Mockery::spy(RequestInterface::class);
        $request->shouldReceive('getUri')->andReturn($uri);
        $request->shouldReceive('getHeaders')->andReturn([]);

        $result = $this->subject->makeCoroutineClient(
            $request,
            ['verify_peername' => false]
        );
        $this->assertInstanceOf(Client::class, $result);
    }
}
