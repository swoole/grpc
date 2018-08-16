<?php

require_once __DIR__ . '/../../vendor/autoload.php';

ini_set('memory_limit', '154M');
co::set(['max_coroutine' => 10002]);
go(function () {
    $kvClient = new Etcdserverpb\KVClient('macos:2379');
    $kvClient->start();
    $request = new Etcdserverpb\PutRequest();
    $request->setPrevKv(true);
    $request->setValue('Swoole');

    $start = microtime(true);
    for ($i = 10000; $i--;) {
        go(function () use ($kvClient, $request, $i) {
            $request->setKey("Hello{$i}");
            list($reply, $status) = $kvClient->Put($request);
            assert($reply->getPrevKv()->getKey() === "Hello{$i}");
            if ($status !== 0) {
                echo "Error#{$status}: {$reply}\n";
                exit;
            }
        });
    }

    // wait all of the responses back
    $kvClient->closeWait();
    echo 'use time: ' . (microtime(true) - $start) . "s\n";
    var_dump($kvClient->stats());
    var_dump(memory_get_usage(true));
});
