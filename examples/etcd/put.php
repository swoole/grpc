<?php

require_once __DIR__ . '/../../vendor/autoload.php';

use Swoole\Coroutine;

Coroutine::create(function () {
    $kvClient = new Etcdserverpb\KVClient(GRPC_SERVER_DEFAULT_URI);
    $request = new Etcdserverpb\PutRequest();
    $request->setPrevKv(true);
    $request->setKey('Hello');
    $request->setValue('Swoole');
    [$reply, $status] = $kvClient->Put($request);
    if ($status === 0) {
        echo "{$reply->getPrevKv()->getKey()}\n";
        echo "{$reply->getPrevKv()->getValue()}\n";
    } else {
        echo "Error#{$status}: {$reply}\n";
    }
    $kvClient->close();
});
