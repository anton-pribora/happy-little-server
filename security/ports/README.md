# Открытые порты

В зависимости от провайдера VDS на вашем виртуальном сервере могут быть предустановлены
разные программы, включая те, которые для Web-сервера не особо нужны. Каждый открытый порт,
это потенциальная угроза безопасности.

Проверить список открытых портов можно с помощью команды `ntstat`:

```
# netstat -lp4n
Active Internet connections (only servers)
Proto Recv-Q Send-Q Local Address           Foreign Address         State       PID/Program name
tcp        0      0 185.12.95.84:53         0.0.0.0:*               LISTEN      16808/named     
tcp        0      0 127.0.0.1:53            0.0.0.0:*               LISTEN      16808/named     
tcp        0      0 0.0.0.0:22              0.0.0.0:*               LISTEN      385/sshd        
tcp        0      0 127.0.0.1:25            0.0.0.0:*               LISTEN      17075/exim4     
tcp        0      0 127.0.0.1:953           0.0.0.0:*               LISTEN      16808/named     
tcp        0      0 0.0.0.0:443             0.0.0.0:*               LISTEN      2302/nginx -g daemo
tcp        0      0 127.0.0.1:3306          0.0.0.0:*               LISTEN      584/mysqld      
tcp        0      0 0.0.0.0:80              0.0.0.0:*               LISTEN      2302/nginx -g daemo
udp        0      0 127.0.0.1:921           0.0.0.0:*                           379/lwresd      
udp        0      0 185.12.95.84:53         0.0.0.0:*                           16808/named     
udp        0      0 127.0.0.1:53            0.0.0.0:*                           16808/named     
```

В идеале в этом списке должны быть только те программы, которые нужны для работы сервера.
Как правило, это mysqld, nginx и sshd.

Отключить ненужные сервисы в Debian можно с помощью утилиты `sysv-rc-conf` (ставится дополнительно):

```
┌ SysV Runlevel Config   -: stop service  =/+: start service  h: help  q: quit ┐
│                                                                              │
│ service      1       2       3       4       5       0       6       S       │
│ ---------------------------------------------------------------------------- │
│ acpid       [ ]     [X]     [X]     [X]     [X]     [ ]     [ ]     [ ]      │
│ atd         [ ]     [X]     [X]     [X]     [X]     [ ]     [ ]     [ ]      │
│ bind9       [ ]     [ ]     [ ]     [ ]     [ ]     [ ]     [ ]     [ ]      │
│ bootlogs    [X]     [X]     [X]     [X]     [X]     [ ]     [ ]     [ ]      │
│ console-s$  [ ]     [ ]     [ ]     [ ]     [ ]     [ ]     [ ]     [X]      │
│ cron        [ ]     [X]     [X]     [X]     [X]     [ ]     [ ]     [ ]      │
│ dbus        [ ]     [X]     [X]     [X]     [X]     [ ]     [ ]     [ ]      │
│ exim4       [ ]     [ ]     [ ]     [ ]     [ ]     [ ]     [ ]     [ ]      │
│ halt        [ ]     [ ]     [ ]     [ ]     [ ]     [ ]     [ ]     [ ]      │
│ irqbalance  [ ]     [X]     [X]     [X]     [X]     [ ]     [ ]     [ ]      │
│ kbd         [ ]     [ ]     [ ]     [ ]     [ ]     [ ]     [ ]     [X]      │
│ keyboard-$  [ ]     [ ]     [ ]     [ ]     [ ]     [ ]     [ ]     [X]      │
│ killprocs   [X]     [ ]     [ ]     [ ]     [ ]     [ ]     [ ]     [ ]      │
│ kmod        [ ]     [ ]     [ ]     [ ]     [ ]     [ ]     [ ]     [X]      │
│ lwresd      [ ]     [ ]     [ ]     [ ]     [ ]     [ ]     [ ]     [ ]      │
│                                                                              │
└──────────────────────────────────────────────────────────────────────────────┘
┌──────────────────────────────────────────────────────────────────────────────┐
│ Use the arrow keys or mouse to move around.      ^n: next pg     ^p: prev pg │
│                        space: toggle service on / off                        │
└──────────────────────────────────────────────────────────────────────────────┘
```

Нужно снять все галочки напротив сервиса и нажать <key>-</key>, чтобы остановить его.

Краткий список сервисов, которые могут быть установлены:

* `bind9` - сервер имён (DNS). Не требуется для работы сайтов.
* `exim4` - почтовый сервер. Не требуется, если письма будет отпрвлять сторонний почтовый сервис (например, Яндекс или Google).
* `lwresd` - лёгкий сервис имён для клиентов bind9. Не требуется для работы сайтов.
* `nfs-common` - клиент/сервер для NFS (сетевая файловая система). Не требуется для работы сайтов.
* `rpcbind`- аналог bind для NFS. Не требуется для работы сайтов.

После отключения сервисов перезагрузите VDS, чтобы проверить, как всё будет работать.
Ещё раз проверьте открытые порты, должно быть так:

```
# netstat -lp4n               
Active Internet connections (only servers)
Proto Recv-Q Send-Q Local Address           Foreign Address         State       PID/Program name
tcp        0      0 0.0.0.0:22              0.0.0.0:*               LISTEN      383/sshd        
tcp        0      0 0.0.0.0:443             0.0.0.0:*               LISTEN      426/nginx -g daemon
tcp        0      0 127.0.0.1:3306          0.0.0.0:*               LISTEN      583/mysqld      
tcp        0      0 0.0.0.0:80              0.0.0.0:*               LISTEN      426/nginx -g daemon
```

## Сканирование портов с помощью nmap

Ещё одна полезная утилита для проверки открытых портов - сетевой сканер `nmap`.

Пример использования:

```
# nmap -A -T4 pribora.info                

Starting Nmap 6.47 ( http://nmap.org ) at 2017-06-30 12:08 +05
Nmap scan report for pribora.info (185.12.95.84)
Host is up (0.0000060s latency).
Not shown: 997 closed ports
PORT    STATE SERVICE  VERSION
22/tcp  open  ssh      OpenSSH 6.7p1 Debian 5+deb8u3 (protocol 2.0)
|_ssh-hostkey: ERROR: Script execution failed (use -d to debug)
80/tcp  open  http     nginx
|_http-methods: No Allow or Public header in OPTIONS response (status code 301)
|_http-title: Did not follow redirect to https://pribora.info/
443/tcp open  ssl/http nginx
|_http-methods: No Allow or Public header in OPTIONS response (status code 405)
|_http-title: pribora.info
| ssl-cert: Subject: commonName=anton-pribora.ru
| Not valid before: 2017-06-28T12:50:00+00:00
|_Not valid after:  2017-09-26T12:50:00+00:00
|_ssl-date: 2035-03-13T16:05:06+00:00; +17y256d8h56m33s from local time.
| tls-nextprotoneg: 
|_  http/1.1
Device type: general purpose
Running: Linux 3.X
OS CPE: cpe:/o:linux:linux_kernel:3
OS details: Linux 3.7 - 3.15
Network Distance: 0 hops
Service Info: OS: Linux; CPE: cpe:/o:linux:linux_kernel

OS and Service detection performed. Please report any incorrect results at http://nmap.org/submit/ .
Nmap done: 1 IP address (1 host up) scanned in 23.15 seconds
```