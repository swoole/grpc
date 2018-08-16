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

/**
 * Represents an active call that sends a single message and then gets a
 * stream of responses.
 */
class ServerStreamingCall extends StreamingCall
{

    public function send($message = null): bool
    {
        if (!$this->streamId) {
            $this->streamId = $this->client->openStream(
                $this->method,
                Parser::pack(Parser::serializeMessage($message))
            );
            return $this->streamId > 0;
        } else {
            trigger_error('ServerStreamingCall can only send once!', E_USER_ERROR);
            return false;
        }
    }

    public function push($message): bool
    {
        trigger_error('ServerStreamingCall can not push data by client!', E_USER_ERROR);
        return false;
    }

}
