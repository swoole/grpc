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
    echo "nghttp -v http://127.0.0.1:{$server->port}\n";
});
$http->on('request', function (swoole_http_request $request, swoole_http_response $response) use ($http) {
    /**@var $request_message HelloRequest */
    $request_message = Grpc\Parser::deserializeMessage([HelloRequest::class, null], $request->rawcontent());
    if ($request_message) {
        $response_message = new HelloReply();
        $response_message->setMessage('Hello ' . $request_message->getName());
        $response->header('content-type', 'application/grpc');
        $response->header('trailer','grpc-status, grpc-message');
        $trailer = [
            "grpc-status" => "0",
            "grpc-message" => ""
        ];
        foreach ($trailer as $trailer_name => $trailer_value) {
            $response->trailer($trailer_name, $trailer_value);
        }
        $response->end(Grpc\Parser::serializeMessage($response_message));
    } else {
        $response->end('failed');
    }
});
$http->start();