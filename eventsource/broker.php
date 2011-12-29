<?php

$context = new ZMQContext();

$pull = $context->getSocket(ZMQ::SOCKET_PULL);
$pull->bind("tcp://*:5555");

$pub = $context->getSocket(ZMQ::SOCKET_PUB);
$pub->bind("tcp://*:5556");

while (true) {
    $msg = $pull->recv();
    echo "publishing received message $msg\n";
    $pub->send($msg);
}
