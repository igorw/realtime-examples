<?php

require __DIR__.'/vendor/.composer/autoload.php';

use Igorw\EventSource\Stream;
use Symfony\Component\HttpFoundation\StreamedResponse;

$app = new Silex\Application();

$app['stream'] = $app->share(function ($app) {
    return new Stream($app['stream.handler']);
});

$app['stream.handler'] = $app->protect(function ($chunk) {
    echo $chunk;
    ob_flush();
    flush();
});

$app->get('/', function () {
    return file_get_contents(__DIR__.'/client.html');
});

$app->get('/stream', function () use ($app) {
    $headers = Stream::getHeaders();

    $stream = function () use ($app, &$response) {
        while (true) {
            $app['stream']
                ->event()
                    ->setData("Hello World")
                ->end()
                ->flush();

            sleep(2);
        }
    };

    return new StreamedResponse($stream, 200, $headers);
});

$app->run();
