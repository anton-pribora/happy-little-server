# Отправка данных на почту через SMTP

Это пример простой реализации скрипта, который отправляет данные на почту
с авторизацией по ключу. Он использует библиотеку [ApMailer](https://github.com/anton-pribora/ApMailer)
для отправки писем через SMTP, но вы вы можете подключить любую другую на своё усмотрение.

## Установка

```
cd /путь/на/вашем/веб-сервере
wget https://github.com/anton-pribora/happy-little-server/raw/master/send-logs-to-mail/server/config.php
wget https://github.com/anton-pribora/happy-little-server/raw/master/send-logs-to-mail/server/index.php
wget https://github.com/anton-pribora/happy-little-server/raw/master/send-logs-to-mail/server/mailerlib.php
```

Отредактировать файл `config.php`.

## Настройка

Конфиг преставляет из себя PHP-скрипт, который должен вернуть массив вида:

```php
[
    'ключ авторизации' => функция отправки данных,
    ...
]
```

При вызове этой функции ей будут переданы данные, которые отправил клиент, 
в качестве первого аргумента. Результатом работы может быть или `true`, или
строка с ошибкой, или массив строк. Если результатом была ошибка,
то она передаётся клиенту с кодом ответа `500`.