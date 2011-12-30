<?php

require __DIR__.'/vendor/.composer/autoload.php';

use Mongrel2\Connection;
use Mongrel2\Request;
use Mongrel2\Tool;

$sender_id = "ab206881-6f49-4276-9db1-1676bfae18b0";
$conn = new Connection($sender_id, "tcp://127.0.0.1:9997", "tcp://127.0.0.1:9996");

$context = $conn->getContext();
$pull = $conn->getRequestSocket();

$sub = $context->getSocket(ZMQ::SOCKET_SUB);
$sub->setSockOpt(ZMQ::SOCKOPT_SUBSCRIBE, "");
$sub->bind("tcp://*:5555");

$poll = new ZMQPoll();
$poll->add($pull, ZMQ::POLL_IN);
$poll->add($sub, ZMQ::POLL_IN);
$readable = $writeable = array();

$users = array();

while (true) {
    $events = $poll->poll($readable, $writeable);
    foreach ($readable as $socket) {
        if ($pull === $socket) {
            $req = $conn->recv();

            $users[$req->conn_id] = $req;

            if ($req->is_disconnect()) {
                unset($users[$req->conn_id]);

                echo sprintf("User %s disconnected\n", $req->conn_id);
                echo sprintf("Current users: %s\n", count($users));

                continue;
            }

            echo "New user connected\n";
            echo sprintf("Current users: %s\n", count($users));

            $headers = array(
                'Content-Type'  => 'text/event-stream',
                'Cache-Control' => 'no-cache',
            );
            $conn->reply($req, Tool::http_response_headers(200, "OK", $headers));
        } else {
            $msg = $sub->recv();
            $event = json_decode($msg, true);

            $reply = '';
            if (isset($event['type'])) {
                $reply .= "event: {$event['type']}\n";
            }
            $reply .= sprintf("data: %s\n\n", json_encode($event['data']));

            echo sprintf("Streaming event: %s\n", json_encode($event['data']));

            foreach ($users as $req) {
                $conn->reply($req, $reply);
            }
        }
    }
}
