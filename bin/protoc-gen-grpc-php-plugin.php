#!/usr/bin/env php
<?php

$file = __DIR__ . '/../vendor/autoload.php';
if (file_exists($file)) {
    require_once $file;
} else if (file_exists($file = __DIR__ . "/../autoload.php")) {
    require_once $file;
} else if (file_exists($file = __DIR__ . "/../../autoload.php")) {
    require_once $file;
} else {
    throw new \RuntimeException("cannot find autoload.php");
}

define('PROJECT_ROOT', __DIR__);

$compiler = new \Gary\Protobuf\Compiler\Compiler();
$compiler->setGenerator(new \Grpc\ServiceGenerator());
$compiler->runAsPlugin();