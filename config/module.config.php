<?php
return [
    'zoop' => [
        'gomi' => [
            //These are all the config options that control the password chagne and recovery system
            'recover_password_token_controller_options' => [

                //name of the shard manifest to use for db operations
                'manifest_name'    => 'default',

                //the FQCN of the user model
                'user_class'       => 'Zoop\GomiModule\DataModel\User',

                //should be the same as the endpoint defined in the shard endpoint_map below
                'endpoint'         => 'recoverpasswordtoken',

                //A service name which will retun a mail transport instance
                //that can be used to send a password recovery email
                'mail_transport'   => 'my_mail_transport_instance',

                //The email address the password recovery email will be sent from
                'mail_from'        => 'zoop@gomimodule.dummy',

                //How long a password recovery token takes to expire
                //in seconds. Defaults to four hours.
                'expiry'           => 4*60*60,

                //Subject of the password recovery email
                'mail_subject'     => 'recover password',

                'email_template'   => 'zoop/gomi/email',

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
                        'extension.accesscontrol' => true,
                        'extension.serializer'    => true,
                        'extension.validator'     => true,
                        'extension.crypt'         => true,
                        'extension.rest'          => [
                            'endpoint_map' => [
                                'recoverpasswordtoken' => [
                                    'class' => 'Zoop\GomiModule\DataModel\RecoverPasswordToken',
                                    'property' => 'code'
                                ]
                            ]
                        ]
                    ],
                    'service_manager_config' => [
                        'factories' => [
                            'crypt.emailaddress' => 'Zoop\GomiModule\Service\CryptEmailAddressFactory'
                        ]
                    ],
                    'documents' => [
                        'Zoop\GomiModule\DataModel' => __DIR__ . '/../src/Zoop/GomiModule/DataModel'
                    ]
                ]
            ]
        ],
    ],

    'controllers' => [
        'factories' => [
            'rest.default.recoverpasswordtoken' => 'Zoop\GomiModule\Service\RecoverPasswordTokenControllerFactory'
        ],
    ],

    'view_manager' => [
        'template_path_stack' => [
            __DIR__ . '/../view',
        ]
    ]
];
