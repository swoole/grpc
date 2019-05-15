<?php

namespace Grpc;

abstract class ClientStub
{
    protected $endpoint = '127.0.0.1:50051';
    protected $grpc_client;

    public function __call($name, $arguments)
    {
        $client = new $this->grpc_client($this->endpoint);
        $client->start();
        list($reply, $status) = call_user_func_array([$client, $name], $arguments);
        $client->close();

        if (!$status) {
            return $reply;
        }

        return null;
    }
}
