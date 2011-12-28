<?php

$context = new ZMQContext();

$sock = $context->getSocket(ZMQ::SOCKET_PUSH);
$sock->bind("tcp://*:5555");

$msg = json_encode(array('type' => 'debug', 'data' => array('foo', 'bar', 'baz')));
$sock->send($msg);
