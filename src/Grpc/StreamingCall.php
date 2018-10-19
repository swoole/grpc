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

class StreamingCall extends BaseCall
{
    public function send($message = null): bool
    {
        if (!$this->streamId) {
            $this->streamId = $this->client->openStream(
                $this->method,
                Parser::serializeMessage($message)
            );
            return $this->streamId > 0;
        } else {
            trigger_error(
                E_USER_WARNING, // warning because it may be a wrong retry
                'You can only send once by a streaming call except connection closed and you retry.'
            );
            return false;
        }
    }

    public function push($message): bool
    {
        if (!$this->streamId) {
            $this->streamId = $this->client->openStream($this->method);
        }
        return $this->client->write($this->streamId, Parser::serializeMessage($message), false);
    }

    public function recv(float $timeout = -1)
    {
        if (!$this->streamId) {
            $recv = false;
        } else {
            $recv = $this->client->recv($this->streamId, $timeout);
            if (!$this->client->isStreamExist($this->streamId)) {
                // stream lost, we need re-push
                $this->streamId = 0;
            }
        }
        return Parser::parseToResultArray($recv, $this->deserialize);
    }

    public function end(): bool
    {
        if (!$this->streamId) {
            return false;
        }
        $ret = $this->client->write($this->streamId, null, true);
        if ($ret) {
            $this->streamId = 0;
        }
        return $ret;
    }

}
