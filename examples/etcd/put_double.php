<?php

require_once __DIR__ . '/../../vendor/autoload.php';

use Swoole\Coroutine;

Coroutine::create(function () {
    $kvClient = new Etcdserverpb\KVClient(GRPC_SERVER_DEFAULT_URI);
    $request = new Etcdserverpb\PutRequest();
    $request->setPrevKv(true);

    $barrier = Coroutine\Barrier::make();

    Coroutine::create(function () use ($kvClient, $request, $barrier) {
        $request->setKey('Hello~');
        $request->setValue('I am Swoole!');
        [$reply, $status] = $kvClient->Put($request);
        if ($status === 0) {
            echo "{$reply->getPrevKv()->getKey()}\n";
            echo "{$reply->getPrevKv()->getValue()}\n";
        } else {
            echo "Error#{$status}: {$reply}\n";
        }
    });

    Coroutine::create(function () use ($kvClient, $request, $barrier) {
        $request->setKey('Hey~');
        $request->setValue('How are u Etcd?');
        [$reply, $status] = $kvClient->Put($request);
        if ($status === 0) {
            echo "{$reply->getPrevKv()->getKey()}\n";
            echo "{$reply->getPrevKv()->getValue()}\n";
        } else {
            echo "Error#{$status}: {$reply}\n";
        }
    });

    // wait all the responses back
    $barrier::wait($barrier);
    $kvClient->close();
});
