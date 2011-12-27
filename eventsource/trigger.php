<?php

$context = new ZMQContext();

$sock = $context->getSocket(ZMQ::SOCKET_PUSH);
$sock->connect("tcp://127.0.0.1:5555");

$msg = json_encode(array('type' => 'debug', 'data' => array('foo', 'bar', 'baz')));
$sock->send($msg);

$msg = json_encode(array('data' => array('foo', 'bar', 'baz')));
$sock->send($msg);
