<?php
return [
    'zoop' => [
        'gomi' => [
            'recover_password_token_controller_options' => [
                'mail_transport' => 'Zoop\GomiModule\MailTransport\File'
            ],
        ],
    ],
    'doctrine' => array(
        'odm' => [
            'configuration' => array(
                'default' => array(
                    'default_db'   => 'gomi-test',
                    'proxy_dir'    => __DIR__ . '/Proxy',
                    'hydrator_dir' => __DIR__ . '/Hydrator',
                )
            ),
        ]
    ),

    'view_manager' => array(
        'display_not_found_reason' => true,
        'display_exceptions' => true,
    ),

    'service_manager' => array(
        'factories' => array(
            'Zoop\GomiModule\MailTransport\File' => function () {
                return new \Zend\Mail\Transport\File(new \Zend\Mail\Transport\FileOptions([
                    'path' => __DIR__ . '/email',
                    'callback' => function () {return 'test_mail.tmp';}
                ]));
            },
        ),
    ),
];
