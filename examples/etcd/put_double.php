<?php

require_once __DIR__ . '/../../vendor/autoload.php';

go(function () {
    $kvClient = new Etcdserverpb\KVClient(GRPC_SERVER_DEFAULT_URI);
    $request = new Etcdserverpb\PutRequest();
    $request->setPrevKv(true);

    go(function () use ($kvClient, $request) {
        $request->setKey('Hello~');
        $request->setValue('I am Swoole!');
        list($reply, $status) = $kvClient->Put($request);
        if ($status === 0) {
            echo "{$reply->getPrevKv()->getKey()}\n";
            echo "{$reply->getPrevKv()->getValue()}\n";
        } else {
            echo "Error#{$status}: {$reply}\n";
        }
    });

    go(function () use ($kvClient, $request) {
        $request->setKey('Hey~');
        $request->setValue('How are u Etcd?');
        list($reply, $status) = $kvClient->Put($request);
        if ($status === 0) {
            echo "{$reply->getPrevKv()->getKey()}\n";
            echo "{$reply->getPrevKv()->getValue()}\n";
        } else {
            echo "Error#{$status}: {$reply}\n";
        }
    });

    // wait all of the responses back
    $kvClient->closeWait();
});
