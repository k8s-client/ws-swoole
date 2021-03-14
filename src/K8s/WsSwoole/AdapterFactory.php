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

use K8s\Core\Contract\ContextConfigInterface;
use K8s\Core\Contract\WebsocketClientFactoryInterface;
use K8s\Core\Websocket\Contract\WebsocketClientInterface;

class AdapterFactory implements WebsocketClientFactoryInterface
{
    /**
     * @var array<string, mixed>
     */
    private $defaults;

    /**
     * @param array<string, mixed> $defaults Any default options to use for the adapter.
     */
    public function __construct(array $defaults = [])
    {
        $this->defaults = $defaults;
    }

    /**
     * @inheritDoc
     */
    public function makeClient(ContextConfigInterface $fullContext): WebsocketClientInterface
    {
        $options = $this->defaults;

        if ($fullContext->getAuthType() === ContextConfigInterface::AUTH_TYPE_CERTIFICATE) {
            $options['ssl_cert_file'] = $fullContext->getClientCertificate();
            $options['ssl_key_file'] = $fullContext->getClientKey();
        }
        if ($fullContext->getServerCertificateAuthority()) {
            $options['ssl_cafile'] = $fullContext->getServerCertificateAuthority();
        }

        return new CoroutineAdapter($options);
    }
}
