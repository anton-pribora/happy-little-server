# Установка PHP

По умолчанию в Debian недоступны воследние версии PHP из соображений безопасности.
Одно из решений проблемы, подключить репозиторий [deb.sury.org](https://packages.sury.org/php/).

## Подключение deb.sury.org

Чтобыподключить репозиторий, выполните команды:

```
apt-get install apt-transport-https ca-certificates lsb-release
wget -O /etc/apt/trusted.gpg.d/php.gpg https://packages.sury.org/php/apt.gpg
echo "deb https://packages.sury.org/php/ `lsb_release -sc` main" > /etc/apt/sources.list.d/php.list
apt-get update
```

После этого станут доступны последние версии PHP:

```
apt-get install php5.6-cli php5.6-curl php5.6-fpm php5.6-gd php5.6-intl php5.6-json php5.6-mbstring php5.6-mcrypt php5.6-pdo-mysql php5.6-xml php5.6-zip
apt-get install php7.1-cli php7.1-curl php7.1-fpm php7.1-gd php7.1-intl php7.1-json php7.1-mbstring php7.1-mcrypt php7.1-pdo-mysql php7.1-xml php7.1-zip
```

Подключение в конфиге NGINX: 

```
server {
    ...
    location ~ ^(.*\.php)$ {
        ...
        fastcgi_pass unix:/run/php/php5.6-fpm.sock;
           или
        fastcgi_pass unix:/run/php/php7.1-fpm.sock;
        ...
    }
}
```