<?php

namespace App\services;

/**
 * The greeting service definition.
 */
interface GreeterInterface
{

    /**
     * Sends a greeting
     * @param \Helloworld\HelloRequest $request
     * @return \Helloworld\HelloReply
     */
    public function SayHello(\Helloworld\HelloRequest $request);

}
