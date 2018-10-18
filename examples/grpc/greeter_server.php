<?php

use Helloworld\HelloReply;
use Helloworld\HelloRequest;

require __DIR__ . '/../../vendor/autoload.php';

$http = new swoole_http_server('0.0.0.0', 50051, SWOOLE_BASE);
$http->set([
    'log_level' => SWOOLE_LOG_INFO,
    'trace_flags' => 0,
    'worker_num' => 1,
    'open_http2_protocol' => true
]);
$http->on('workerStart', function (swoole_http_server $server) {
    echo "php " . __DIR__ . "/greeter_client.php\n";
});
$http->on('request', function (swoole_http_request $request, swoole_http_response $response) use ($http) {
    $path = $request->server['request_uri'];
    $route = [
        '/helloworld.Greeter/SayHello' => function (...$args) {
            [$server, $request, $response] = $args;
            /**@var $request_message HelloRequest */
            $request_message = Grpc\Parser::deserializeMessage([HelloRequest::class, null], $request->rawContent());
            if ($request_message) {
                $response_message = new HelloReply();
                $response_message->setMessage('Hello ' . $request_message->getName());
                $response->header('content-type', 'application/grpc');
                $response->header('trailer', 'grpc-status, grpc-message');
                $trailer = [
                    "grpc-status" => "0",
                    "grpc-message" => ""
                ];
                foreach ($trailer as $trailer_name => $trailer_value) {
                    $response->trailer($trailer_name, $trailer_value);
                }
                $response->end(Grpc\Parser::serializeMessage($response_message));
                return true;
            }
            return false;
        }
    ];
    if (!(isset($route[$path]) && $route[$path]($http, $request, $response))) {
        $response->status(400);
        $response->end('Bad Request');
    }
});
$http->start();