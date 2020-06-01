<?php
/*
  +----------------------------------------------------------------------+
  | Swoole-gRPC                                                   |
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
 * Represents an active call that sends a stream of messages and then gets
 * a single response.
 */
class ClientStreamingCall extends StreamingCall
{
    private $received = false;

    public function recv(float $timeout = GRPC_DEFAULT_TIMEOUT)
    {
        if (!$this->received) {
            $this->received = true;
            return parent::recv($timeout);
        }
        trigger_error('ClientStreamingCall can only recv once!', E_USER_ERROR);
        return false;
    }

}
