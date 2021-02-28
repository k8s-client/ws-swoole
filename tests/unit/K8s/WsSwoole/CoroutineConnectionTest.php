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

use K8s\WsSwoole\CoroutineConnection;
use Swoole\Coroutine\Http\Client;

class CoroutineConnectionTest extends TestCase
{
    /**
     * @var \Mockery\LegacyMockInterface|\Mockery\MockInterface|Client
     */
    private $client;

    /**
     * @var CoroutineConnection
     */
    private $subject;

    public function setUp(): void
    {
        parent::setUp();
        $this->client = \Mockery::spy(Client::class);
        $this->subject = new CoroutineConnection($this->client);
    }

    public function testItSends(): void
    {
        $this->subject->send('foo');
        $this->client->shouldHaveReceived('push', ['foo']);
    }

    public function testItCloses(): void
    {
        $this->subject->close();
        $this->client->shouldHaveReceived('close');
    }
}
