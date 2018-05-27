<?php
require __DIR__ . '/../vendor/autoload.php';
require 'controller.php';

$server = new \Jacwright\RestServer\RestServer('debug');
$server->addClass('Controllers');
$server->handle();
