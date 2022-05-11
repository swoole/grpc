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

use InvalidArgumentException;
use RuntimeException;
use Swoole\Coroutine;
use Swoole\Coroutine\Channel;
use Swoole\Coroutine\Http2\Client as Http2Client;
use Swoole\Http2\Response as Http2Response;

class Client
{
    /**
     * The stats of all clients
     * @var array
     */
    protected static $numStats = [
        'constructed_num' => 0,
        'destructed_num' => 0,
    ];

    protected static $debug = false;

    // ====== send =====
    /**
     * The channel to proxy send data from all of the coroutine
     * @var Channel
     */
    protected $sendChannel;
    /**
     * The channel to get the current send stream id (as ret val)
     * @var Channel
     */
    protected $sendRetChannel;
    /**
     * To record the cid of send loop coroutine
     * @var int
     */
    protected $sendCid = 0;

    // ===== recv ======
    /**
     * The hashMap of channels [streamId => response]
     * @var Channel[]
     */
    protected $recvChannelMap = [];
    /**
     * To record the cid of recv loop coroutine
     * @var int
     */
    protected $recvCid = 0;
    /**
     * The sign of if this Client is closing
     * @var int
     */
    protected $closing = 0;
    /**
     * @var Channel
     */
    protected $closeWaiter;

    protected $host;
    protected $port;
    protected $ssl;
    protected $opts;
    /**
     * @var Http2Client
     */
    protected $client;
    protected $timeout;

    public static function numStats(): array
    {
        return static::$numStats;
    }

    public static function debug(bool $enable = true): void
    {
        static::$debug = $enable;
    }

    public function __construct(string $hostname, array $opts = [])
    {
        if (stripos($hostname, 'unix:/') === 0) {
            $parts['host'] = $hostname;
            $parts['port'] = 0;
        } else {
            // parse url
            $parts = parse_url($hostname);
        }

        if (!$parts || !isset($parts['host']) || !isset($parts['port'])) {
            throw new InvalidArgumentException("The hostname {$hostname} is illegal!");
        }

        $this->host = $parts['host'];
        $this->port = $parts['port'];

        $default_opts = [
            'timeout' => GRPC_DEFAULT_TIMEOUT,
            'ssl' => false,
            'ssl_host_name' => ''
        ];
        $this->opts = $opts + $default_opts;
        $this->timeout = &$this->opts['timeout'];
        $this->ssl = &$this->opts['ssl'];
        $this->ssl = !!$this->ssl || !!$this->opts['ssl_host_name'];

        // create client and init settings
        $this->client = new Http2Client($this->host, $this->port, $this->ssl);
        $this->client->set($this->opts);
        static::$numStats['constructed_num']++;

        $this->start();
    }

    protected function start()
    {
        if ($this->recvCid !== 0 || $this->sendCid !== 0) {
            throw new RuntimeException('Client has been started');
        }
        if (Coroutine::getuid() <= 0) {
            throw new RuntimeException('Client must be start it in an alone coroutine');
        }
        if (!$this->client->connect()) {
            throw new RuntimeException('Connect failed, error=' . $this->client->errMsg, $this->client->errCode);
        }
        // receive wait
        Coroutine::create(function () {
            $this->recvCid = Coroutine::getuid();
            // start recv loop
            while (true) {
                /* @var Http2Response $response */
                $response = $this->client->recv(-1);
                if (static::$debug) {
                    var_dump($response);
                    if ($response === false && $this->client->errCode !== 0) {
                        var_dump($this->client->errCode);
                        var_dump($this->client->errMsg);
                    }
                }

                if ($response !== false) {
                    // normal response
                    if ($the_channel = $this->recvChannelMap[$response->streamId] ?? null) {
                        // we can find the waiting coroutine and return response to it
                        $the_channel->push($response);

                        if (!$response->pipeline) {
                            // unregister channel
                            unset($this->recvChannelMap[$response->streamId]);
                        }
                    } // else: receiver not found, discard it

                    // push finished, check if close wait and no coroutine is waiting, if Y, stop recv loop
                    if (!$this->closing || !empty($this->recvChannelMap)) {
                        continue;
                    }
                }

                // if you want to close it or retry connect failed, stop recv loop
                if ($this->closing) {
                    $need_break = true;
                } else {
                    $need_break = !$this->client->connect();
                }

                // ↑↓ We must `retry-connect` before we push `false` response
                // ↑↓ Then the pop channel coroutine can knows that if this client is available

                // clear all, we will auto reconnect, but it need user retry again by himself
                if (!empty($this->recvChannelMap)) {
                    foreach ($this->recvChannelMap as $the_channel) {
                        $the_channel->close();
                    }
                    $this->recvChannelMap = [];
                }

                if ($need_break) {
                    break;
                }
            }

            $this->recvCid = 0;
            $this->closed();
        });

        // send wait
        Coroutine::create(function () {
            $this->sendCid = Coroutine::getuid();
            $this->sendChannel = new Channel(0);
            $this->sendRetChannel = new Channel(0);
            while (true) {
                $sendData = $this->sendChannel->pop(-1);
                if ($sendData === false) {
                    break;
                } elseif ($sendData instanceof Request) {
                    $ret = $this->client->send($sendData);
                } else {
                    $ret = $this->client->write(...$sendData);
                }
                $this->sendRetChannel->push($ret);
            }
            $this->sendRetChannel->close();

            $this->sendCid = 0;
            $this->closed();
        });
    }

    public function __get(string $name)
    {
        return $this->client->$name ?? null;
    }

    public function __destruct()
    {
        static::$numStats['destructed_num']++;
    }

    /**
     * @return int|array
     */
    public function stats($key = null)
    {
        return $this->client->stats($key);
    }

    public function isConnected(): bool
    {
        return $this->client->connected;
    }

    public function isRunning(): bool
    {
        return $this->recvCid > 0 || $this->sendCid > 0;
    }

    public function isStreamExist(int $streamId): bool
    {
        return isset($this->recvChannelMap[$streamId]);
    }

    public function getClient(): Http2Client
    {
        return $this->client;
    }

    public function getTimeout(): float
    {
        return $this->timeout;
    }

    public function setTimeout(float $timeout): void
    {
        $this->timeout = $timeout;
    }

    /**
     * Open a stream and return it's id
     *
     * @param string $path
     * @param string $data
     * @param string $method
     * @return int
     */
    public function openStream(string $path, $data = '', string $method = '', bool $use_pipeline_read = false): int
    {
        $request = new Request;
        if ($method) {
            $request->method = $method;
        } else {
            if (!$data) {
                $request->method = 'GET';
            } else {
                $request->method = 'POST';
            }
        }
        $request->path = $path;
        if ($data) {
            $request->data = $data;
        }
        $request->pipeline = true;
        if ($use_pipeline_read) {
            if (SWOOLE_VERSION_ID < 40503) {
                throw new InvalidArgumentException('Require Swoole version >= 4.5.3');
            }
            $request->usePipelineRead = true;
        }

        return $this->send($request);
    }

    public function send(Request $request): int
    {
        $this->sendChannel->push($request);
        $streamId = $this->sendRetChannel->pop();
        if ($streamId > 0) {
            $this->recvChannelMap[$streamId] = new Channel;
        }

        return $streamId;
    }

    public function write(int $streamId, $data, bool $end = false): bool
    {
        return $this->sendChannel->push([$streamId, $data, $end]) && $this->sendRetChannel->pop();
    }

    public function recv(int $streamId, float $timeout = null)
    {
        $channel = $this->recvChannelMap[$streamId] ?? null;
        if ($channel) {
            $response = $channel->pop($timeout === null ? $this->timeout : $timeout);
            // timeout
            if ($response === false && $channel->errCode === -1) {
                unset($this->recvChannelMap[$streamId]);
            }

            return $response;
        }

        // the channel is not exist or you recv too late
        return false;
    }

    public function close(): void
    {
        if ($this->closing) {
            return;
        }
        $this->closing = 2;
        // close write side first
        $this->sendChannel->close();
        $this->client->close();
    }

    protected function closed(): void
    {
        if ($this->closing > 0) {
            $this->closing--;
        }
        // close success and notify the close waiter
        if ($this->closeWaiter) {
            $closeWaiter = $this->closeWaiter;
            $this->closeWaiter = null;
            $closeWaiter->push(true);
        }
    }

    public function closeWait(): void
    {
        if ($this->closing) {
            return;
        }
        $this->closing = 2;
        $this->closeWaiter = $closeWaiter = new Channel;
        $n = 0;
        if ($this->recvCid > 0) {
            $n++;
        }
        if ($this->sendCid > 0) {
            $n++;
        }
        while ($n--) {
            $closeWaiter->pop();
        }
    }
}
