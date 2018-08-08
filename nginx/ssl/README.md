# Настройка SSL

Более подробная статья на английском языке [https://www.digitalocean.com/community/tutorials/how-to-secure-nginx-with-let-s-encrypt-on-ubuntu-14-04](https://www.digitalocean.com/community/tutorials/how-to-secure-nginx-with-let-s-encrypt-on-ubuntu-14-04)

## Let's Encrypt

> [Let’s Encrypt](https://letsencrypt.org/) is a free, automated, and open Certificate Authority.

Let’s Encrypt - хороший способ получить бесплатный сертификат. Весь процесс автоматизирован
и не требует регистрации и оплаты.

### Установка certbot-auto

[certbot](https://certbot.eff.org/) - консольный клиент, который получает сертификат и заботится о его актуальности.

В последних версиях Debian он есть в стандартных пакетах, ставится обычным путём:

```
apt-get install certbot 
``` 

Если его нет в пакетах, то можно установить через [репозиторий](https://certbot.eff.org/docs/install.html#operating-system-packages)
или просто скачав исходный код:

```
wget https://dl.eff.org/certbot-auto
chmod +x ./certbot
mv ./certbot /usr/local/bin
```

При первом запуске `certbot-auto` установит все необходимые пакеты, после чего будет готов к работе.

### Получение сертификата

Чтобы получить сертификат нужно выполнить следующую команду:

```
certbot certonly --webroot -w /var/lib/letsencrypt/public \
    -d example.org -d www.example.org \
    -d other_domain -d ...
```

Параметры означают:

* `certonly` - нужно выписать сертификат.
* `--webroot` - авторизация должна быть через плагин Webroot. Этот способ не привязан к веб-серверу и достаточно
универсален.
* `-w ПУТЬ` - путь к корневой папке домена.
* `-d ДОМЕН` - имя домена, на который выписывается сертификат. Если для домена используется свой webroot,
то он должен указываться через ключ `-w` перед ним, например так - `-w webroot1 -d domain1 -w webroot2 -d domain2`.

Перед выпиской сертификата Let's Encrypt проверяет, что ваш домен действительно находится на том сервере,
откуда запускается бот. Для этого бот создаёт ключ в папке webroot/.well-known, а сервер авторизации
пытается его получить по адресу `http://ваш_домен/.well-known/ключ`. Если ключ получен, то сертификат
выписывается, в противном случае возвращается ошибка.

Для NGINX самый удобный способ пройти авторизацию - вынести папку `.well-known` в отдельное место
и указывать её для всех доменов. Для этого нужно создать папку `mkdir -p /var/lib/letsencrypt/public/` и
в конфиге хоста NGINX указать:

```
server {
    ...
    location /.well-known/ {
        root /var/lib/letsencrypt/public/;
    }
    ...
}
```

После этого можно выписывать сертификат.

### Обновление сертификата

Сертификаты Let's Encrypt выдаются на три месяца, после чего их надо обновлять. Чтобы автоматизировать
этот процесс, нужно добавить в крон команду `certbot-auto renew`:

```
echo '35 5 * * * root /usr/local/bin/certbot renew > /dev/null' > /etc/cron.d/cert
echo '45 5 * * * root /etc/init.d/nginx reload 2>&1 1>/dev/null' >> /etc/cron.d/cert
```

Если по каким-либо причинам сертифкат не сможет обновиться, то придёт уведомление на почту,
которая была указана при его получении.


## Использование сертификата

Сгенерируйте ключ dhparam.key:

```
openssl dhparam -out /etc/ssl/certs/dhparam.pem 2048
```

Укажите в конфиге хоста следующую информацию:

```
server {
    listen 443 ssl;
    ...
    ssl_certificate /etc/letsencrypt/live/ВАШ_ДОМЕН/fullchain.pem;
    ssl_certificate_key /etc/letsencrypt/live/ВАШ_ДОМЕН/privkey.pem;
    
    ssl_protocols TLSv1 TLSv1.1 TLSv1.2;
    ssl_prefer_server_ciphers on;
    ssl_dhparam /etc/ssl/certs/dhparam.pem;
    ssl_ciphers 'ECDHE-RSA-AES128-GCM-SHA256:ECDHE-ECDSA-AES128-GCM-SHA256:ECDHE-RSA-AES256-GCM-SHA384:ECDHE-ECDSA-AES256-GCM-SHA384:DHE-RSA-AES128-GCM-SHA256:DHE-DSS-AES128-GCM-SHA256:kEDH+AESGCM:ECDHE-RSA-AES128-SHA256:ECDHE-ECDSA-AES128-SHA256:ECDHE-RSA-AES128-SHA:ECDHE-ECDSA-AES128-SHA:ECDHE-RSA-AES256-SHA384:ECDHE-ECDSA-AES256-SHA384:ECDHE-RSA-AES256-SHA:ECDHE-ECDSA-AES256-SHA:DHE-RSA-AES128-SHA256:DHE-RSA-AES128-SHA:DHE-DSS-AES128-SHA256:DHE-RSA-AES256-SHA256:DHE-DSS-AES256-SHA:DHE-RSA-AES256-SHA:AES128-GCM-SHA256:AES256-GCM-SHA384:AES128-SHA256:AES256-SHA256:AES128-SHA:AES256-SHA:AES:CAMELLIA:DES-CBC3-SHA:!aNULL:!eNULL:!EXPORT:!DES:!RC4:!MD5:!PSK:!aECDH:!EDH-DSS-DES-CBC3-SHA:!EDH-RSA-DES-CBC3-SHA:!KRB5-DES-CBC3-SHA';
    ssl_session_timeout 1d;
    ssl_session_cache shared:SSL:50m;
    ssl_stapling on;
    ssl_stapling_verify on;
    add_header Strict-Transport-Security max-age=15768000;
    ...
}
```

Замените `ВАШ_ДОМЕН` на имя папки, в которой находятся ваши ключи.

## Дополнительно

Поскольку для всех доменов Let's Encrypt выдаёт один сертификат, то можно вынести информацию по SSL
в сниппет. Для этого создайте файл `/etc/nginx/snippets/ssl.conf` и перенесите туда настройки `ssl_...`.
После этого в конфиге хоста подключите сниппет:

```
server {
    listen 443 ssl;
    ...
    include snippets/ssl.conf;
    ...
}
```

