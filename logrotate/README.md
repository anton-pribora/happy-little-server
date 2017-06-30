# Ротация логов

Для ротации логов используется системная утилита `logrotate`.

## Установка

```
cd /etc/logrotate.d/
wget https://github.com/anton-pribora/happy-little-server/raw/master/logrotate/www
```

## Дополнительно

Конфиг представляет из себя текстовый файл следующего содержания:

```
/www/*/logs/*.log {
        monthly
        rotate 4
        compress
        create

        postrotate
                [ ! -f /var/run/nginx.pid ] || kill -USR1 `cat /var/run/nginx.pid`
        endscript
}
```

В резльтате логи должны ротироваться каждый месяц со сжатием. Храниться будут последние 4 лога.

Для поиска информации по сжатому логу используйте `zgrep`.