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

namespace K8s\WsSwoole;

use K8s\Core\Websocket\Contract\WebsocketConnectionInterface;
use Swoole\Coroutine\Http\Client;

class CoroutineConnection implements WebsocketConnectionInterface
{
    /**
     * @var Client
     */
    private $client;

    public function __construct(Client $client)
    {
        $this->client = $client;
    }

    public function close(): void
    {
        $this->client->close();
    }

    public function send(string $data): void
    {
        $this->client->push($data);
    }
}
