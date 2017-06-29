<?php

$config = require __DIR__ .'/config.php';

$param = function ($name, $default = NULL) {
    return isset($_POST[$name]) ? $_POST[$name] : $default;
};

$key  = $param('key');
$data = $param('data');

if (empty($key)) {
    http_response_code(400);
    die('You must specify "key" param');
}

if (empty($data)) {
    http_response_code(400);
    die('You must specify "data" param');
}

if (!isset($config[$key])) {
    http_response_code(403);
    die('Key not found');
}

$send   = $config[$key];
$result = $send($data);

if ($result !== true) {
    http_response_code(500);
    if ($result) {
        echo 'Some errors: '. join('; ', (array) $result);
    }
}