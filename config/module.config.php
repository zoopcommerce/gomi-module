<?php

return [
    'zoop' => [
        'gomi' => [
            //These are all the config options that control the password chagne and recovery system
            'recover_password_token_controller_options' => [
                //the FQCN of the user model
                'user_class' => 'Zoop\GomiModule\DataModel\User',
                //should be the same as the endpoint defined in the shard endpoint_map below
                'endpoint' => 'recoverpasswordtoken',
                //A service name which will retun a mail transport instance
                //that can be used to send a password recovery email
                'mail_transport' => 'my_mail_transport_instance',
                //The email address the password recovery email will be sent from
                'mail_from' => 'zoop@gomimodule.dummy',
                //How long a password recovery token takes to expire
                //in seconds. Defaults to four hours.
                'expiry' => 4 * 60 * 60,
                //Subject of the password recovery email
                'mail_subject' => 'recover password',
                'email_template' => 'zoop/gomi/email',
                'start_recovery_template' => 'zoop/gomi/start-recovery',
                'new_password_template' => 'zoop/gomi/new-password',
                'recovery_complete_template' => 'zoop/gomi/recovery-complete',
                'email_sent_template' => 'zoop/gomi/email-sent',
            ],
            'crypt_email_address' => [

                //A user's email address is encrypted in the database. This is the salt used
                //for encryption. Please change it in your production app
                'salt' => 'change this salt in your own app',
                //The key used for email address encryption
                'key' => 'change this key phrase in your own app',
            ]
        ],
        'shard' => [
            'manifest' => [
                'default' => [
                    'extension_configs' => [
                        'extension.odmcore' => true,
                        'extension.accesscontrol' => true,
                        'extension.serializer' => true,
                        'extension.validator' => true,
                        'extension.crypt' => true,
                    ],
                    'service_manager_config' => [
                        'factories' => [
                            'crypt.emailaddress' => 'Zoop\GomiModule\Service\CryptEmailAddressFactory',
                        ]
                    ],
                    'models' => [
                        'Zoop\GomiModule\DataModel' => __DIR__ . '/../src/Zoop/GomiModule/DataModel'
                    ]
                ]
            ],
            'rest' => [
                'options_class' => 'Zoop\GomiModule\Options\RecoverPasswordTokenControllerOptions',
                'templates' => [
                    'create' => 'zoop/gomi/email-sent',
                    'get' => 'zoop/gomi/new-password',
                    'getList' => 'zoop/gomi/start-recovery',
                    'update' => 'zoop/gomi/recovery-complete',
                ],
                'rest' => [
                    'recoverpasswordtoken' => [
                        'manifest' => 'default',
                        'class' => 'Zoop\GomiModule\DataModel\RecoverPasswordToken',
                        'property' => 'code',
                        'listeners' => [
                            'create' => [
                                'zoop.gomi.listener.unserialize',
                                'zoop.shardmodule.listener.create',
                                'zoop.shardmodule.listener.flush',
                                'zoop.gomi.listener.email',
                                'zoop.shardmodule.listener.prepareviewmodel'
                            ],
                            'delete' => [],
                            'deleteList' => [],
                            'get' => [
                                'zoop.gomi.listener.prepareviewmodel'
                            ],
                            'getList' => [
                                'zoop.gomi.listener.prepareviewmodel'
                            ],
                            'patch' => [],
                            'patchList' => [],
                            'update' => [
                                'zoop.gomi.listener.unserialize',
                                'zoop.gomi.listener.prepareviewmodel'
                            ],
                            'replaceList' => [],
                            'options' => [],
                        ],
                    ]
                ]
            ]
        ]
    ],
    'service_manager' => [
        'factories' => [
        ],
        'invokables' => [
            'zoop.gomi.listener.unserialize' => 'Zoop\GomiModule\Controller\Listener\UnserializeListener',
            'zoop.gomi.listener.email' => 'Zoop\GomiModule\Controller\Listener\EmailListener',
            'zoop.shardmodule.restcontrollermap' => 'Zoop\GomiModule\RestControllerMap',
            'zoop.gomi.listener.prepareviewmodel' => 'Zoop\GomiModule\Controller\Listener\PrepareViewModelListener',
        ],
    ],
    'view_manager' => [
        'template_path_stack' => [
            __DIR__ . '/../view',
        ]
    ]
];
