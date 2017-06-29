<?php

require __DIR__ .'/mailerlib.php';

Mailer()->init([
    'defaultFrom' => 'no-reply@example.org',
    'transports'  => [
        ['file', 'dir'  => __DIR__ .'/mails'],
//      ['smtp', 'login' => '****@yandex.ru', 'password' => '******', 'host' => 'smtp.yandex.ru', 'ssl' => true, 'port' => '465'],
    ],
]);

return [
    'AAAAAAAAAAAAAAAAAAAAAAAAA' => function ($data) {
        $message = Mailer()->newTextMessage()
            ->setSubject('Тестовые логи')
            ->addRecipient('my-email@example.org')
            ->setContent($data)
        ;
        
        return Mailer()->sendMessage($message) ? true : Mailer()->lastErrors();
    },
];