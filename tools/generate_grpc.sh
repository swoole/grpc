#!/usr/bin/env bash

current=$(pwd)

./generator \
--proto_path=$current/../src/Grpc/Proto \
--php_out=$current/../src/Grpc \
--grpc_out=$current/../src/Grpc \
--grpc_php_out=$current/../src/Grpc/Services \
--plugin=protoc-gen-grpc=$current/$1 \
--custom_plugin=protoc-gen-grpc-php=$current/$2 \
$current/../src/Grpc/Proto