<?php
co::set([
    'log_level' => SWOOLE_LOG_INFO,
    'trace_flags' => 0,
]);

use Etcdserverpb\WatchCreateRequest;
use Etcdserverpb\WatchCreateRequest\FilterType;
use Etcdserverpb\WatchRequest;

require_once __DIR__ . '/../../vendor/autoload.php';

// The Watcher
go(function () {
    $watchClient = new Etcdserverpb\WatchClient(GRPC_SERVER_DEFAULT_URI);

    $watchCall = $watchClient->Watch();
    $request = new WatchRequest();
    $createRequest = new WatchCreateRequest();
    $createRequest->setKey('Hello');
    $request->setCreateRequest($createRequest);

    _retry:
    $watchCall->push($request);
    /**@var $reply Etcdserverpb\WatchResponse */
    while (true) {
        [$reply, $status] = $watchCall->recv();
        if ($status === 0) { // success
            if ($reply->getCreated() || $reply->getCanceled()) {
                continue;
            }
            foreach ($reply->getEvents() as $event) {
                /**@var $event Mvccpb\Event */
                $type = $event->getType();
                $kv = $event->getKv();
                if (FilterType::NOPUT === $type) {
                    echo "Put key {$kv->getKey()} => {$kv->getValue()}\n";
                    break;
                } elseif (FilterType::NODELETE === $type) {
                    echo "Delete key {$kv->getKey()}\n";
                    break;
                }
            }
        } else { // failed
            static $retry_time = 0;
            if ($watchClient->isConnected()) {
                $retry_time++;
                echo "Retry#{$retry_time}\n";
                goto _retry;
            } else {
                echo "Error#{$status}: {$reply}\n";
                break;
            }
        }
    }
    $watchClient->close();
});

// The Writer Put and Delete
go(function () {
    $kvClient = new Etcdserverpb\KVClient(GRPC_SERVER_DEFAULT_URI);
    go(function () use ($kvClient) {
        $request = new Etcdserverpb\PutRequest();
        $request->setKey('Hello');
        $request->setPrevKv(true);
        while (true) {
            static $count = 0;
            co::sleep(.5);
            $request->setValue('Swoole#' . (++$count));
            [$reply, $status] = $kvClient->Put($request);
            if ($status !== 0) {
                echo "Error#{$status}: {$reply}\n";
                break;
            }
        }
        $kvClient->close();
    });
    go(function () use ($kvClient) {
        $request = new Etcdserverpb\DeleteRangeRequest();
        $request->setKey('Hello');
        $request->setPrevKv(true);
        while (true) {
            co::sleep(1);
            [$reply, $status] = $kvClient->DeleteRange($request);
            if ($status !== 0) {
                echo "Error#{$status}: {$reply}\n";
                break;
            }
        }
        $kvClient->close();
    });
});