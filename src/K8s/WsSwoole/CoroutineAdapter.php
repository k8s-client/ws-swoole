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

use K8s\Core\Exception\WebsocketException;
use K8s\Core\Websocket\Contract\FrameHandlerInterface;
use K8s\Core\Websocket\Contract\WebsocketClientInterface;
use K8s\Core\Websocket\Frame;
use Psr\Http\Message\RequestInterface;
use Swoole\Coroutine;
use Swoole\Coroutine\System;
use Throwable;

class CoroutineAdapter implements WebsocketClientInterface
{
    /**
     * @var ClientFactory
     */
    private $clientFactory;

    /**
     * @var array
     */
    private $options;

    public function __construct(array $options = [], ?ClientFactory $clientFactory = null)
    {
        $this->clientFactory = $clientFactory ?? new ClientFactory();
        $this->options = $options;
    }

    public function connect(RequestInterface $request, FrameHandlerInterface $payloadHandler): void
    {
        # Swoole does not bubble up exceptions for some reason. So we need to get it ourselves.
        $exception = null;

        $connector = function () use ($payloadHandler, $request, &$exception) {
            try {
                $this->sendAndReceive($request, $payloadHandler);
            } catch (Throwable $e) {
                $exception = $e;
            }
        };

        $cid = Coroutine::getCid();

        if ($cid > -1) {
            $connector();
        } else {
            Coroutine\run(function () use ($connector) {
                go($connector);
            });
        }

        /** @var Throwable|null $exception */
        if ($exception) {
            throw $exception;
        }
    }

    /**
     * @throws WebsocketException
     */
    private function sendAndReceive(RequestInterface $request, FrameHandlerInterface $payloadHandler): void
    {
        $client = $this->clientFactory->makeCoroutineClient($request, $this->options);

        $path = $request->getUri()->getPath();
        if ($request->getUri()->getQuery()) {
            $path .= '?' . $request->getUri()->getQuery();
        }

        $result = $client->upgrade($path);
        if ($result === false) {
            throw new WebsocketException((string)$client->body);
        }

        $connection = new CoroutineConnection($client);
        $payloadHandler->onConnect($connection);

        $isComplete = false;
        while (!$isComplete) {
            /** @var \Swoole\WebSocket\Frame|null $response */
            $response = $client->recv();

            if ($response && $response->opcode === 2) {
                $payloadHandler->onReceive(
                    new Frame(
                        (int)$response->opcode,
                        strlen((string)$response->data),
                        (string)$response->data
                    ),
                    $connection
                );
            } elseif ($response && $response->opcode === 6) {
                $isComplete = true;
                $payloadHandler->onClose();
            } else {
                $isComplete = true;
            }

            System::sleep(0.01);
        }
    }
}
