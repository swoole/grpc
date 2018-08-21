<?php
/**
 * Author: Twosee <twose@qq.com>
 * Date: 2018/8/13 下午9:02
 */

namespace Grpc;

use Swoole\Coroutine\Channel;

class ChannelPool extends \SplQueue
{
    private static $instance;

    public static function getInstance(): self
    {
        return static::$instance ?? (static::$instance = new ChannelPool);
    }

    public function get(): Channel
    {
        return $this->isEmpty() ? new Channel(0) : $this->pop();
    }

    public function put(Channel $channel)
    {
        $channel->errCode = 0;
        $this->push($channel);
    }

}