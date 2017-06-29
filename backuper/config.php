<?php

return [
    'backup_root' => '/backups',
    'hosts' => [
        'anton-pribora.ru' => [
            'folder' => [
                'docs' => ['/www/dev-anton-pribora.ru/docs', 'exclude' => 'public/asset/*'],
            ],
            'mysql'  => [
                'db' => ['compress' => true, 'dumps_limit' => 5, 'dbname' => 'anton-pribora', 'user' => 'test', 'password' => 'test', 'host' => 'localhost'],
            ],
        ],
    ],
];