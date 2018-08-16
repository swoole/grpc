<?php

require_once __DIR__ . '/../../vendor/autoload.php';

go(function () {
    $grpcClient = new Grpc\Client('127.0.0.1:2379');
    $grpcClient->start();
    // use in different type clients

    go(function () use ($grpcClient) {
        $kvClient = new Etcdserverpb\KVClient('127.0.0.1:2379', ['use' => $grpcClient]);
        $request = new Etcdserverpb\PutRequest();
        $request->setPrevKv(true);
        $request->setKey('Hello');
        $request->setValue('Swoole');
        list($reply, $status) = $kvClient->Put($request);
        if ($status === 0) {
            echo "\n=== PUT KV OK ===\n";
        } else {
            echo "Error#{$status}: {$reply}\n";
        }
    });

    go(function () use ($grpcClient) {
        $authClient = new Etcdserverpb\AuthClient('127.0.0.1:2379', ['use' => $grpcClient]);

        $userRequest = new Etcdserverpb\AuthUserAddRequest();
        $userNames = ['rango', 'twosee', 'gxh', 'stone', 'sjl'];
        foreach ($userNames as $username) {
            $userRequest->setName($username);
            list($reply, $status) = $authClient->UserAdd($userRequest);
            if ($status !== 0) {
                goto _error;
            }
        }

        $useListRequest = new Etcdserverpb\AuthUserListRequest();
        list($reply, $status) = $authClient->UserList($useListRequest);
        if ($status !== 0) {
            goto _error;
        }
        echo "\n=== SHOW USER LIST ===\n";
        foreach ($reply->getUsers() as $user) {
            /**@var \Authpb\User */
            echo "* {$user}\n";
        }
        echo "=== SHOW USER LIST OK ===\n";

        $userRequest = new Etcdserverpb\AuthUserDeleteRequest();
        foreach ($userNames as $username) {
            $userRequest->setName($username);
            list($reply, $status) = $authClient->UserDelete($userRequest);
            if ($status !== 0) {
                goto _error;
            }
        }

        if (false) {
            _error:
            echo "Error#{$status}: {$reply}\n";
        }

        echo "\n=== SHOW ALL CLIENT STATS ===\n";
        var_dump(grpc_client_num_stats());
        $grpcClient->close();
    });

});