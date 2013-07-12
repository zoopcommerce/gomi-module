<?php
return array(
    'zoop' => [
        'gomi' => [
            'user_controller_options' => [
                'document_manager' => 'doctrine.odm.documentmanager.default',
                'document_class' => 'Zoop\GomiModule\DataModel\User',
                'limit' => 30 //max number of records returned from getList
            ],
            'recover_password_token_controller_options' => [
                'document_manager' => 'doctrine.odm.documentmanager.default',
                'endpoint'         => 'recoverpasswordtoken',
                'manifest_name'    => 'default',
                'user_class'       => 'Zoop\GomiModule\DataModel\User',
                'mail_transport'   => 'my_mail_transport_instance',
                'mail_from'        => 'zoop@gomimodule.dummy',
                'expiry'           => 4*60*60, //time in seconds
                'mail_subject'     => 'recover password',
            ],
            'crypt_email' => [
                'salt' => 'qw4q35varyw456vaertwqetsvtruerraw45q3s',
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
                                'user'              => [
                                    'class' => 'Zoop\GomiModule\DataModel\User',
                                    'property' => 'username'
                                ],
                                'recoverpasswordtoken' => [
                                    'class' => 'Zoop\GomiModule\DataModel\RecoverPasswordToken',
                                    'property' => 'code'
                                ]
                            ]
                        ]
                    ],
                    'service_manager_config' => [
                        'factories' => [
                            'crypt.email' => 'Zoop\GomiModule\Service\CryptEmailFactory'
                        ]
                    ]
                ]
            ]
        ],
    ],

    'controllers' => array(
        'factories' => array(
            'rest.default.recoverpasswordtoken' => 'Zoop\GomiModule\Service\RecoverPasswordTokenControllerFactory'
        ),
    ),

    'doctrine' => array(
        'driver' => array(
            'default' => array(
                'drivers' => array(
                    'Zoop\GomiModule\DataModel' => 'doctrine.driver.user'
                ),
            ),
            'user' => array(
                'class' => 'Doctrine\ODM\MongoDB\Mapping\Driver\AnnotationDriver',
                'paths' => array(
                    __DIR__ . '/../src/Zoop/GomiModule/DataModel'
                ),
            ),
        ),
    ),

    'view_manager' => array(
        'template_map'             => array(
            'email/recover-password' => __DIR__ . '/../view/email/recover-password.phtml',
            'zoop/recover-password/recover-password' => __DIR__ . '/../view/zoop/recover-password/recover-password.phtml',
        ),
        'template_path_stack' => array(
            __DIR__ . '/../view',
        )
    )
);
