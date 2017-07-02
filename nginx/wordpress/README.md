# Настройка хоста для Wordpress

```
server {
    listen 80;
    server_name ВАШ_ДОМЕН;
    
    return 301 https://ВАШ_ДОМЕН$request_uri;
}
    
server {
    listen 443 ssl;
    server_name ВАШ_ДОМЕН;

    include snippets/ssl.conf;

    root       /www/ВАШ_ДОМЕН/docs;
    access_log /www/ВАШ_ДОМЕН/logs/access.log;
    error_log  /www/ВАШ_ДОМЕН/logs/error.log;
    
    set $phpini "
        error_log=/www/ВАШ_ДОМЕН/logs/php-errors.log
    ";

    index index.php index.html;

    location / {
        try_files $uri $uri/ /index.php?$args;
    }
    
    location ~* \.(js|css|png|jpg|jpeg|gif|ico)$ {
        expires max;
        log_not_found off;
    }
    
    location ^~ /wp-content/uploads {}
    
    location ~ ^(.*\.php)$ {
        include fastcgi_params;
        fastcgi_param PHP_VALUE $phpini;
        fastcgi_param SCRIPT_FILENAME $document_root$1;
        fastcgi_pass unix:/run/php5-fpm.sock;
    }
}
```