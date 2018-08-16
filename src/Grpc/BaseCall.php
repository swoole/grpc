<?php
/*
  +----------------------------------------------------------------------+
  | Swoole-Etcd-Client                                                   |
  +----------------------------------------------------------------------+
  | This source file is subject to version 2.0 of the Apache license,    |
  | that is bundled with this package in the file LICENSE, and is        |
  | available through the world-wide-web at the following url:           |
  | http://www.apache.org/licenses/LICENSE-2.0.html                      |
  | If you did not receive a copy of the Apache2.0 license and are unable|
  | to obtain it through the world-wide-web, please send a note to       |
  | license@swoole.com so we can mail you a copy immediately.            |
  +----------------------------------------------------------------------+
  | Author: Twosee <twose@qq.com>                                        |
  +----------------------------------------------------------------------+
*/

namespace Grpc;

class BaseCall
{
    /** @var Client */
    protected $client;
    /** @var string */
    protected $method;
    /** @var mixed */
    protected $deserialize;
    /** @var int */
    protected $streamId;

    public function setClient($client)
    {
        $this->client = $client;
    }

    public function setMethod(string $method)
    {
        $this->method = $method;
    }

    public function setDeserialize($deserialize)
    {
        $this->deserialize = $deserialize;
    }

    public function getStreamId(): int
    {
        return $this->streamId;
    }

    public function setStreamId(int $streamId)
    {
        $this->streamId = $streamId;
    }

}