<?php

namespace App\services;

/**
 * The greeting service definition.
 */
class GreeterService extends \SwFwLess\services\GrpcUnaryService implements \App\services\GreeterInterface
{

    /**
     * Sends a greeting
     * @param \Helloworld\HelloRequest $request
     * @return \Helloworld\HelloReply
     */
    public function SayHello(\Helloworld\HelloRequest $request)
    {
        //todo implements interface
    }

}
