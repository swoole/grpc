#!/usr/bin/env bash
__CURRENT__=`pwd`
__DIR__=$(cd "$(dirname "$0")";pwd)

cd ${__DIR__} &&
./generator \
--proto_path=./../src/Grpc/Proto \
--php_out=./../src/Grpc \
--grpc_out=./../src/Grpc \
--grpc_php_out=./src/Grpc \
--plugin=protoc-gen-grpc=${__CURRENT__}/$1 \
./../src/Grpc/Proto
cd ${__CURRENT__}