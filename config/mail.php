<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Default Mailer
    |--------------------------------------------------------------------------
    |
    | This option controls the default mailer that is used to send any email
    | messages sent by your application. Alternative mailers may be setup
    | and used as needed; however, this mailer will be used by default.
    |
    */

    'default' => env('MAIL_MAILER', 'smtp'),

    /*
    |--------------------------------------------------------------------------
    | Mailer Configurations
    |--------------------------------------------------------------------------
    |
    | Here you may configure all of the mailers used by your application plus
    | their respective settings. Several examples have been configured for
    | you and you are free to add your own as your application requires.
    |
    | Laravel supports a variety of mail "transport" drivers to be used while
    | sending an e-mail. You will specify which one you are using for your
    | mailers below. You are free to add additional mailers as required.
    |
    | Supported: "smtp", "sendmail", "mailgun", "ses",
    |            "postmark", "log", "array", "failover"
    |
    */

    'mailers' => [
        'notification' => [
            'transport' => 'smtp',
            'host' => env('MAIL_HOST', 'smtp.mailgun.org'),
            'port' => env('MAIL_PORT', 587),
            'encryption' => env('MAIL_ENCRYPTION', 'tls'),
            'username' => env('MAIL_USERNAME'),
            'password' => env('MAIL_PASSWORD'),
            'timeout' => null,
            'local_domain' => env('MAIL_EHLO_DOMAIN'),
            'from' => [
                'address' => env('MAIL_FROM_ADDRESS'),
                'name' => 'Notificação Age Telecom'
            ]
        ],
        'portal' => [
            'transport' => 'smtp',
            'host' => env('MAIL_HOST_PORTAL', 'smtp.mailgun.org'),
            'port' => env('MAIL_PORT_PORTAL', 587),
            'encryption' => env('MAIL_ENCRYPTION_PORTAL', 'tls'),
            'username' => env('MAIL_USERNAME_PORTAL'),
            'password' => env('MAIL_PASSWORD_PORTAL'),
            'timeout' => null,
            'local_domain' => env('MAIL_EHLO_DOMAIN'),
            'from' => [
                'address' => env('MAIL_FROM_ADDRESS_PORTAL'),
                'name' => 'Portal Age Telecom'
            ]
        ],
        'sac' => [
            'transport' => 'smtp',
            'host' => env('MAIL_HOST_SAC', 'smtp.mailgun.org'),
            'port' => env('MAIL_PORT_SAC', 587),
            'encryption' => env('MAIL_ENCRYPTION_SAC', 'tls'),
            'username' => env('MAIL_USERNAME_SAC'),
            'password' => env('MAIL_PASSWORD_SAC'),
            'timeout' => null,
            'local_domain' => env('MAIL_EHLO_DOMAIN'),
            'from' => [
                'address' => env('MAIL_FROM_ADDRESS_SAC'),
                'name' => 'Sac Age Telecom'
            ]
        ],
        'b2b' => [
            'transport' => 'smtp',
            'host' => env('MAIL_HOST_B2B', 'smtp.mailgun.org'),
            'port' => env('MAIL_PORT_B2B', 587),
            'encryption' => env('MAIL_ENCRYPTION_B2B', 'tls'),
            'username' => env('MAIL_USERNAME_B2B'),
            'password' => env('MAIL_PASSWORD_B2B'),
            'timeout' => null,
            'local_domain' => env('MAIL_EHLO_DOMAIN'),
            'from' => [
                'address' => env('MAIL_FROM_ADDRESS_B2B'),
                'name' => 'Age Empresas'
            ]
        ],
        'fat' => [
            'transport' => 'smtp',
            'host' => env('MAIL_HOST_FAT', 'smtp.mailgun.org'),
            'port' => env('MAIL_PORT_FAT', 587),
            'encryption' => env('MAIL_ENCRYPTION_FAT', 'tls'),
            'username' => env('MAIL_USERNAME_FAT'),
            'password' => env('MAIL_PASSWORD_FAT'),
            'timeout' => null,
            'local_domain' => env('MAIL_EHLO_DOMAIN'),
            'from' => [
                'address' => env('MAIL_FROM_ADDRESS_FAT'),
                'name' => 'Aviso Age Telecom'
            ]
        ],
        'warning' => [
            'transport' => 'smtp',
            'host' => env('MAIL_HOST_WARNING', 'smtp.mailgun.org'),
            'port' => env('MAIL_PORT_WARNING', 587),
            'encryption' => env('MAIL_ENCRYPTION_WARNING', 'tls'),
            'username' => env('MAIL_USERNAME_WARNING'),
            'password' => env('MAIL_PASSWORD_WARNING'),
            'timeout' => null,
            'local_domain' => env('MAIL_EHLO_DOMAIN'),
            'from' => [
                'address' => env('MAIL_FROM_ADDRESS_WARNING'),
                'name' => 'Aviso Age Telecom'
            ]
        ],
        'contact' => [
            'transport' => 'smtp',
            'host' => env('MAIL_HOST_CONTACT', 'smtp.mailgun.org'),
            'port' => env('MAIL_PORT_CONTACT', 587),
            'encryption' => env('MAIL_ENCRYPTION_CONTACT', 'tls'),
            'username' => env('MAIL_USERNAME_CONTACT'),
            'password' => env('MAIL_PASSWORD_CONTACT'),
            'timeout' => null,
            'local_domain' => env('MAIL_EHLO_DOMAIN'),
            'from' => [
                'address' => env('MAIL_FROM_ADDRESS_CONTACT'),
                'name' => 'Contato Age Telecom'
            ]
        ],
        'ses' => [
            'transport' => 'ses',
        ],

        'mailgun' => [
            'transport' => 'mailgun',
        ],

        'postmark' => [
            'transport' => 'postmark',
        ],

        'sendmail' => [
            'transport' => 'sendmail',
            'path' => env('MAIL_SENDMAIL_PATH', '/usr/sbin/sendmail -bs -i'),
        ],

        'log' => [
            'transport' => 'log',
            'channel' => env('MAIL_LOG_CHANNEL'),
        ],

        'array' => [
            'transport' => 'array',
        ],

        'failover' => [
            'transport' => 'failover',
            'mailers' => [
                'smtp',
                'log',
            ],
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Global "From" Address
    |--------------------------------------------------------------------------
    |
    | You may wish for all e-mails sent by your application to be sent from
    | the same address. Here, you may specify a name and address that is
    | used globally for all e-mails that are sent by your application.
    |
    */



    /*
    |--------------------------------------------------------------------------
    | Markdown Mail Settings
    |--------------------------------------------------------------------------
    |
    | If you are using Markdown based email rendering, you may configure your
    | theme and component paths here, allowing you to customize the design
    | of the emails. Or, you may simply stick with the Laravel defaults!
    |
    */

    'markdown' => [
        'theme' => 'default',

        'paths' => [
            resource_path('views/vendor/mail'),
        ],
    ],

];
