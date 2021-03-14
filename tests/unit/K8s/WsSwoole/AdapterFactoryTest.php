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

use K8s\Core\Contract\ContextConfigInterface;
use K8s\WsSwoole\AdapterFactory;
use K8s\WsSwoole\CoroutineAdapter;

class AdapterFactoryTest extends TestCase
{
    /**
     * @var AdapterFactory
     */
    private $subject;

    public function setUp(): void
    {
        parent::setUp();
        $this->subject = new AdapterFactory();
    }

    public function testItCanMakeTheAdapter(): void
    {
        $config = \Mockery::spy(ContextConfigInterface::class);
        $config->shouldReceive([
            'getClientCertificate' => '/client.crt',
            'getClientKey' => '/client.key',
            'getServerCertificateAuthority' => '/server.ca',
        ]);

        $result = $this->subject->makeClient($config);
        $this->assertInstanceOf(CoroutineAdapter::class, $result);
    }
}
