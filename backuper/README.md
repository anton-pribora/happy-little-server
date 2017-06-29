# Создание резеврных копий баз данных и файлов

`backuper.php` - программа, которая запускает консольные команды для создания резервных копий сайтов.
Она умеет синхронизировать папки, используя `rsync`. И экспортировать базы данных MySQL, используя `mysqldump`.

## Установка

Для работы программы требуются `rsync`, `bzip2` и `mysqdump`. Если их нет в системе, то их нужно установить.

@todo

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

## Пример работы

```
% php /backups/backuper.php
anton-pribora.ru:
   rsync --perms --times --delete -og --recursive --exclude='public/asset/*' '/www/dev-anton-pribora.ru/docs/' '/backups/anton-pribora.ru/docs/daily'
   mysqldump -R -u'test' -p'test' -h'localhost' 'anton-pribora' > /backups/anton-pribora.ru/db/2017-06-29_22-16-08.db.sql
   /bin/bzip2 -f '/backups/anton-pribora.ru/db/2017-06-29_22-16-08.db.sql'
   rm '/backups/anton-pribora.ru/db/2017-06-29_22-15-56.db.sql.bz2'
```