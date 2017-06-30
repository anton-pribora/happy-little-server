# Хост по умолчанию

Любой публичный сервер подвергается сканированию с целью найти уязвимые места.
И чем меньше таких мест будет, тем лучше. Поэтому все нецелевые запросы надо
отсекать заранее.

Самый нецелевой запрос тот, у которого отсутствует или задано неверно имя хоста.
Чтобы обработать такие запросы нужно сделать хост по умолчанию.  

## Установка

@todo

## Дополнительно

Конфиг представляет из себя текстовый файл для NGINX следующего содержания:

```
server {
    listen 80 default_server;

    return 400;
    
    root       /www/default/docs;
    access_log /www/default/logs/access.log;
    error_log  /www/default/logs/error.log;
}

server {
    listen 443 ssl default_server;

    include snippets/ssl.conf;
    
    return 400;
    
    root       /www/default/docs;
    access_log /www/default/logs/access.log;
    error_log  /www/default/logs/error.log;
}
```

В результате на любой запрос с ~неизвестным~ или ~отсутствующим~ именем хоста сервер будет возвращать `400 Bad request`.