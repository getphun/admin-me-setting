<?php
/**
 * admin-me-setting config file
 * @package admin-me-setting
 * @version 0.0.1
 * @upgrade true
 */

return [
    '__name' => 'admin-me-setting',
    '__version' => '0.0.1',
    '__git' => 'https://github.com/getphun/admin-me-setting',
    '__files' => [
        'modules/admin-me-setting' => [ 'install', 'remove', 'update' ],
        'theme/admin/me/setting'   => [ 'install', 'remove', 'update' ],
        'theme/admin/static/js/admin-me-setting.js' => [ 'install', 'remove', 'update' ],
        'theme/admin/static/css/admin-me-setting.css' => [ 'install', 'remove', 'update' ],
        'theme/mailer/admin-me-setting/verify-email.phtml' => [ 'install', 'remove' ]
    ],
    '__dependencies' => [
        'admin'
    ],
    '_services' => [],
    '_autoload' => [
        'classes' => [
            'AdminMeSetting\\Controller\\SettingController' => 'modules/admin-me-setting/controller/SettingController.php',
            'AdminMeSetting\\Controller\\AdminMeController' => 'modules/admin-me-setting/controller/AdminMeController.php',
            'AdminMeSetting\\Controller\\EmailController'   => 'modules/admin-me-setting/controller/EmailController.php',
            'AdminMeSetting\\Controller\\PhoneController'   => 'modules/admin-me-setting/controller/PhoneController.php',
            'AdminMeSetting\\Controller\\SocialController'  => 'modules/admin-me-setting/controller/SocialController.php'
        ],
        'files' => []
    ],
    
    '_routes' => [
        'admin' => [
            'adminMeSetting' => [
                'rule' => '/me/setting',
                'handler' => 'AdminMeSetting\\Controller\\Setting::profile'
            ],
            
            'adminMePassword' => [
                'rule' => '/me/setting/password',
                'handler' => 'AdminMeSetting\\Controller\\Setting::password'
            ],
            
            'adminMeEmail' => [
                'rule' => '/me/setting/email',
                'handler' => 'AdminMeSetting\\Controller\\Email::index'
            ],
            'adminMeEmailPrimary' => [
                'rule' => '/me/setting/email/:id/primary',
                'handler' => 'AdminMeSetting\\Controller\\Email::primary'
            ],
            'adminMeEmailRemove' => [
                'rule' => '/me/setting/email/:id/remove',
                'handler' => 'AdminMeSetting\\Controller\\Email::remove'
            ],
            'adminMeEmailVerify' => [
                'rule' => '/me/setting/email/:id/verify',
                'handler' => 'AdminMeSetting\\Controller\\Email::verify'
            ],
            'adminMeEmailVerifyConfirm' => [
                'rule' => '/me/setting/email/:id/confirm/:code',
                'handler' => 'AdminMeSetting\\Controller\\Email::verifyConfirm'
            ],
            
            'adminMePhone' => [
                'rule' => '/me/setting/phone',
                'handler' => 'AdminMeSetting\\Controller\\Phone::index'
            ],
            'adminMePhonePrimary' => [
                'rule' => '/me/setting/phone/:id/primary',
                'handler' => 'AdminMeSetting\\Controller\\Phone::primary'
            ],
            'adminMePhoneRemove' => [
                'rule' => '/me/setting/phone/:id/remove',
                'handler' => 'AdminMeSetting\\Controller\\Phone::remove'
            ],
            
            'adminMeSocial' => [
                'rule' => '/me/setting/social',
                'handler' => 'AdminMeSetting\\Controller\\Social::index'
            ],
            'adminMeSocialEdit' => [
                'rule' => '/me/setting/social/:id',
                'handler' => 'AdminMeSetting\\Controller\\Social::edit'
            ],
            'adminMeSocialRemove' => [
                'rule' => '/me/setting/social/:id/remove',
                'handler' => 'AdminMeSetting\\Controller\\Social::remove'
            ]
        ]
    ],
    
    'form' => [
        'admin-me-email' => [
            'email' => [
                'type'  => 'email',
                'label' => 'Email',
                'rules' => [
                    'required'  => true,
                    'email'     => true,
                    'unique'    => [
                        'model' => 'UserEmail\\Model\\UserEmail',
                        'field' => 'address'
                    ]
                ]
            ]
        ],
        
        'admin-me-password' => [
            'current_password' => [
                'type'  => 'password',
                'label' => 'Current Password',
                'rules' => [
                    'required' => true
                ]
            ],
            'new_password' => [
                'type'  => 'password',
                'label' => 'New Password',
                'attrs' => [
                    'data-meter' => 'yup'
                ],
                'rules' => [
                    'required'  => true,
                    'length'    => [
                        'min' => 6
                    ]
                ]
            ],
            'retype_password' => [
                'type'  => 'password',
                'label' => 'Retype Password',
                'rules' => [
                    'required' => true
                ]
            ],
            'truncate_session' => [
                'type'  => 'checkbox',
                'label' => 'Log me out from everywhere',
                'rules' => []
            ]
        ],
        'admin-me-phone' => [
            'phone' => [
                'type'  => 'phone',
                'label' => 'Phone',
                'rules' => [
                    'required'  => true,
                    'regex'     => '!^\+([0-9- ]+)[0-9]$!',
                    'unique'    => [
                        'model' => 'UserPhone\\Model\\UserPhone',
                        'field' => 'number'
                    ]
                ]
            ]
        ],
        'admin-me-setting' => [
            'name' => [
                'type'  => 'text',
                'label' => 'Username',
                'rules' => [
                    'required' => true,
                    'alnumdash' => true,
                    'unique' => [
                        'model' => 'User\\Model\\User',
                        'field' => 'name',
                        'self'  => [
                            'service' => 'user',
                            'field'   => 'id'
                        ]
                    ]
                ],
                'form-position' => 'left'
            ],
            'fullname' => [
                'type'  => 'text',
                'label' => 'Fullname',
                'rules' => [
                    'required' => true
                ],
                'form-position' => 'left'
            ]
        ],
        'admin-me-social' => [
            'page'  => [
                'type'    => 'url',
                'label'   => 'Social Account Page',
                'rules'   => [
                    'url'   => true
                ]
            ],
            'vendor' => [
                'type'  => 'select',
                'label' => 'Vendor',
                'rules' => [],
                'options' => [
                    'facebook'      => 'Facebook',
                    'instagram'     => 'Instagram',
                    'linkedin'      => 'LinkedIn',
                    'myspace'       => 'MySpace',
                    'pinterest'     => 'Pinterest',
                    'plus.google'   => 'Google Plus',
                    'soundcloud'    => 'SoundCloud',
                    'tumblr'        => 'Tumblr',
                    'twitter'       => 'Twitter',
                    'wordpress'     => 'Wordpress',
                    'youtube'       => 'Youtube'
                ]
            ]
        ]
    ]
];