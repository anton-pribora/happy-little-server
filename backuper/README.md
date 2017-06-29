# Создание резеврных копий баз данных и файлов

`backuper.php` - программа, которая запускает консольные команды для создания резервных копий сайтов.
Она умеет синхронизировать папки, используя `rsync`. И экспортировать базы данных MySQL, используя `mysqldump`.

## Установка

Для установки программы выполните следующие команды:

```
cd /backups
wget https://github.com/anton-pribora/happy-little-server/raw/master/backuper/backuper.php
wget https://github.com/anton-pribora/happy-little-server/raw/master/backuper/config.php
```

Отредактируйте `config.php` под свои настройки.

Также для работы программы требуются `rsync`, `bzip2` и `mysqdump`. Если их нет в системе, то установите их, 
испольузя системный установщик.

## Запуск

Для запуска из консоли в ручном режиме выполните команду:

```
php /backups/backuper.php
```

Для запуска раз в сутки добавьте задание в `cron`:

```
echo '30 2 * * * root php /backups/backuper.php 2>&1 > /dev/null' > /etc/cron.d/backuper
```

## Настройка

Конфиг представляет из себя PHP-скрипт, который возвращает массив настроек. В нём необходимо определить список хостов.
В хосте нужно указать информацию о папках (ключ `folder`) и базах данных (ключ `mysql`).

```php
// Файл config.php

return [
    // Папка, где будут храниться резервные копии
    'backup_root' => '/backups',
    
    // Список хостов для резервного копирования
    'hosts' => [
    
        // Ключ является именем хоста и будет подставляться после backup_root
        'my-site' => [
            // Список папок для копирования
            'folder' => [
                // Полное копирование папки
                'one'   => '/some/dir',
                
                // Копирование с исключением одного шаблона
                'two'   => ['/some/other/dir', 'exclude' => 'public/asset/*'],
                
                // Копирование с исключением нескольких шаблонов
                'three' => ['/some/else/dir', 'exclude' => ['tmp/*', 'my/other/path/*.txt']],
            ],
            
            // Список баз данных mysql
            'mysql'  => [
                // Экспорт баз данных со сжатием и ограничением на количество дампов в 5 штук
                'db1' => ['compress' => true, 'dumps_limit' => 5, 'dbname' => 'my-db1', 'user' => 'test', 'password' => '123', 'host' => 'localhost'],
                'db2' => ['compress' => true, 'dumps_limit' => 5, 'dbname' => 'my-db2', 'user' => 'test', 'password' => '123', 'host' => 'localhost'],
            ],
        ],
    ],
];
```

## Особенности работы

Резервные копии папок создаются через синхронизацию `rsync` без дальнейшей обработки. В отличе от баз
данных резервная копия папки всегда одна. Это связано с тем, что размер файлов может быть достаточно большим,
и множественное копирование может привести к отсутствию свободного места.

## Пример работы

```
% php /backups/backuper.php
anton-pribora.ru:
   mkdir -p '/backups/anton-pribora.ru/docs/daily'; ionice rsync --perms --times --delete -og --recursive --exclude='public/asset/*' '/www/anton-pribora.ru/docs/' '/backups/anton-pribora.ru/docs/daily'
   mkdir -p '/backups/anton-pribora.ru/db'; mysqldump -R -u'test' -p'test' -h'localhost' 'anton-pribora' > /backups/anton-pribora.ru/db/2017-06-29_22-52-14.db.sql
   nice /bin/bzip2 -f '/backups/anton-pribora.ru/db/2017-06-29_22-52-14.db.sql'
```