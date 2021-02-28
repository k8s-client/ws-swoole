# k8s-ws-swoole

This library provides a Swoole based websocket adapter for the `k8s/client` library.

## General Use with the K8s library / Configuration Options

1. Install the library:

`composer require k8s/ws-swoole`

**Note**: If you don't need to change any TLS settings, this is all that is needed most likely.

2. If you need to configure any options, then you can use the below method to set those and use the websocket with them:

```php
use K8s\Client\K8s;
use K8s\Client\Options;
use K8s\WsSwoole\CoroutineAdapter;

$options = [
    # May need to toggle SSL verification settings if using a self-signed cert, like for Minikube
    'ssl_allow_self_signed' => true,
    'ssl_verify_peer' => false,
    'verify_peer' => false,
    'verify_peer_name' => false,
    # To use certificate based auth, you may need to pass in the cert locations.
    'ssl_cert_file' => '<home-dir>/.minikube/profiles/minikube/client.crt',
    'ssl_key_file' => '/<home-dir>/.minikube/profiles/minikube/client.key',
];

$websocket = new CoroutineAdapter($options);

# You can then pass the new websocket adapter in the options to be used
$options = new Options('k8s.endpoint.local');
$options->setWebsocketClient($websocket);

# Construct K8s to use the new websocket in the options
$k8s = new K8s($options);
```
