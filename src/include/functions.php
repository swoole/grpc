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

const GRPC_DEFAULT_TIMEOUT = 3.0;
const GRPC_ERROR_NO_RESPONSE = -1;
define('GRPC_SERVER_DEFAULT_URI', ($tmp = getenv('GRPC_SERVER_DEFAULT_URI')) ? $tmp : '127.0.0.1:2379');

function grpc_client_num_stats(): array
{
    return \Grpc\Client::numStats();
}

function grpc_client_debug(bool $enable = true)
{
    \Grpc\Client::debug($enable);
}
