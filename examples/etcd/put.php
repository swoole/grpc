<?php

require_once __DIR__ . '/../../vendor/autoload.php';

go(function () {
    $kvClient = new Etcdserverpb\KVClient(GRPC_SERVER_DEFAULT_URI);
    $request = new Etcdserverpb\PutRequest();
    $request->setPrevKv(true);
    $request->setKey('Hello');
    $request->setValue('Swoole');
    list($reply, $status) = $kvClient->Put($request);
    if ($status === 0) {
        echo "{$reply->getPrevKv()->getKey()}\n";
        echo "{$reply->getPrevKv()->getValue()}\n";
    } else {
        echo "Error#{$status}: {$reply}\n";
    }
    $kvClient->close();
});
