#!/usr/bin/env bash
__CURRENT__=`pwd`
__DIR__=$(cd "$(dirname "$0")";pwd)

cd ${__DIR__} &&
./generator \
--proto_path=./../src/Etcd/Proto  \
--php_out=./../src/Etcd \
--grpc_out=./../src/Etcd \
--plugin=protoc-gen-grpc=${__CURRENT__}/$1  \
./../src/Etcd/Proto
cd ${__CURRENT__}