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

use Psr\Http\Message\RequestInterface;
use Swoole\Coroutine\Http\Client;

class ClientFactory
{
    public function makeCoroutineClient(RequestInterface $request, array $options = []): Client
    {
        $ssl = $request->getUri()->getScheme() === 'wss';

        $client = new Client(
            $request->getUri()->getHost(),
            $request->getUri()->getPort() ?? ($ssl ? 443 : 80),
            $ssl
        );

        if (!empty($options)) {
            $client->set($options);
        }

        $client->setHeaders(array_map(function ($value) {
            return $value[0];
        }, $request->getHeaders()));

        return $client;
    }
}
