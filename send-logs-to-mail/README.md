# Отправка логов на почту через SMTP

Отправка логов делится на [клиента](client) и [сервер](server). Клиент собирает информацию
о логах, которые были изменены, и передаёт серверу, который отправляет эти данные конечному 
получателю. Авторизация происходит по ключу.

Таким образом клиент не хранит логины и пароли от почты, а только ключ и адрес
сервера отправки данных. Это исключает несанкционированный доступ к почте администратора
третьими лицами.

## Установка

### Установка сборщика логов

```
cd /www
wget https://github.com/anton-pribora/happy-little-server/raw/master/send-logs-to-mail/client/logposter.php
wget https://github.com/anton-pribora/happy-little-server/raw/master/send-logs-to-mail/client/logposter-config.php
```

Отправка логов раз в час через `cron`:

```
echo '20 * * * * root php /www/logposter.php > /dev/null' > /etc/cron.d/logposter
```

Отредактировать файл `logposter-config.php`.

### Установка сервера обработки данных

```
cd /путь/на/вашем/веб-сервере
wget https://github.com/anton-pribora/happy-little-server/raw/master/send-logs-to-mail/server/config.php
wget https://github.com/anton-pribora/happy-little-server/raw/master/send-logs-to-mail/server/index.php
wget https://github.com/anton-pribora/happy-little-server/raw/master/send-logs-to-mail/server/mailerlib.php
```

Отредактировать файл `config.php`.

### Генерирование случайного ключа

```
% head -200 /dev/urandom | sha256sum 
3c503ba68877e4d266ed22d75b781a6e2196c998b02f329e23834219973ea161  -
```

## Пример настройки

Допустим, вы являетесь администратором 3 сайтов A, B и C. Сайты A и B вы разрабатываете для
клиентов, а C ваш собственный. На сайтах A и B вы размещаете `logposter.php` с конфигом, 
где указываете адрес вашего сервера и ключ доступа. Примерно так:

```php
// logposter-config.php

// Для сайта A
return [
    [
        'url'     => 'http://site-C/send-logs-to-mail/server/',
        'params'  => ['key' => 'AAAAAAAAAAAAAAAAAAAAAAAAA',],
        'storage' => __DIR__ .'/logposter.json',
        'logs'    => [
            '/www/*/logs/php-errors.log',
        ],
    ],
];

// Для сайта B
return [
    [
        'url'     => 'http://site-C/send-logs-to-mail/server/',
        'params'  => ['key' => 'BBBBBBBBBBBBBBBBBBBBBBBBB',],
        'storage' => __DIR__ .'/logposter.json',
        'logs'    => [
            '/www/*/logs/php-errors.log',
        ],
    ],
];
```

На своём сайте вы размещаете код сервера, где прописываете ключи доступа для сайтов A и B:

```php
// config.php

return [
    // Отправка логов клиента A
    'AAAAAAAAAAAAAAAAAAAAAAAAA' => function ($data) {
        // ...
    },
    
    // Отправка логов клиента B
    'BBBBBBBBBBBBBBBBBBBBBBBBB' => function ($data) {
        // ...
    },
];

```

Если какой-то клиент отказывается от ваших услуг, то вы просто блокируете его ключ
на своём сайте.