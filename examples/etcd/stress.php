<?php

require_once __DIR__ . '/../../vendor/autoload.php';

use Swoole\Coroutine;

ini_set('memory_limit', '154M');
Coroutine::set(['max_coroutine' => 10002]);
Coroutine::create(function () {
    $kvClient = new Etcdserverpb\KVClient(GRPC_SERVER_DEFAULT_URI);
    $request = new Etcdserverpb\PutRequest();
    $request->setPrevKv(true);
    $request->setValue('Swoole');

    $barrier = Coroutine\Barrier::make();
    $start = microtime(true);
    for ($i = 10000; $i--;) {
        Coroutine::create(function () use ($kvClient, $request, $i, $barrier) {
            $request->setKey("Hello{$i}");
            [$reply, $status] = $kvClient->Put($request);
            assert($reply->getPrevKv()->getKey() === "Hello{$i}");
            if ($status !== 0) {
                echo "Error#{$status}: {$reply}\n";
                exit;
            }
        });
    }

    // wait all the responses back
    $barrier::wait($barrier);
    $kvClient->close();
    echo 'use time: ' . (microtime(true) - $start) . "s\n";
    var_dump($kvClient->stats());
    var_dump(memory_get_usage(true));
});
