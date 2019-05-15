<?php

namespace App\services;

/**
 * The greeting service definition.
 *
 * @mixin \Helloworld\GreeterClient
 */
class GreeterClient  extends \Grpc\ClientStub
{

    use \SwFwLess\components\traits\Singleton;

    protected $grpc_client = \Helloworld\GreeterClient::class;

}
