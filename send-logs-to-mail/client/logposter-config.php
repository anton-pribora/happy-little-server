<?php

return [
    [
        'url'     => 'http://happy-little-server/send-logs-to-mail/server/',
        'params'  => ['key' => 'AAAAAAAAAAAAAAAAAAAAAAAAA',],
        'storage' => __DIR__ .'/logposter.json',
        'logs'    => [
            '/www/*/logs/php-errors.log',
        ],
    ],
];