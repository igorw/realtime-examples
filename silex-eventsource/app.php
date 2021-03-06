<?php

require __DIR__.'/vendor/.composer/autoload.php';

use Igorw\EventSource\Stream;

$app = new Silex\Application();

$app['stream'] = $app->share(function ($app) {
    return new Stream();
});

$app->get('/', function () {
    return file_get_contents(__DIR__.'/client.html');
});

$app->get('/stream', function () use ($app) {
    $headers = Stream::getHeaders();

    $stream = function () use ($app) {
        set_time_limit(0);
        ini_set('memory_limit', '512M');

        while (true) {
            $app['stream']
                ->event()
                    ->setData("Hello World")
                ->end()
                ->flush();

            sleep(5);
        }
    };

    return $app->stream($stream, 200, $headers);
});

$app->run();
