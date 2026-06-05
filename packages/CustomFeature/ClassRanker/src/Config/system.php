<?php

return [
    /**
     * Class Ranker.
     */
    [
        'key'  => 'class_ranker',
        'name' => 'Class Ranker',
        'info' => 'Class Ranker module for student',
        'sort' => 1,
    ], [
        'key'  => 'class_ranker.settings',
        'name' => 'Settings',
        'info' => 'Settings for Class Ranker module',
        'icon' => 'settings/store.svg',
        'sort' => 1,
    ], [
        'key'    => 'class_ranker.settings.sms_service',
        'name'   => 'SMS Service',
        'info'   => 'Settings for SMS service used in Class Ranker module',
        'sort'   => 1,
        'fields' => [
            [
                'name'    => 'status',
                'title'   => 'Status',
                'type'    => 'boolean',
                'default' => true,
            ], [
                'name'       => 'template_id',
                'title'      => 'Template ID',
                'type'       => 'text',
            ], [
                'name'       => 'sender_id',
                'title'      => 'Sender ID',
                'type'       => 'text',
            ], [
                'name'    => 'auth_key',
                'title'   => 'Authentication Key',
                'type'    => 'text',
            ],
        ],
    ], [
        'key'    => 'class_ranker.settings.pay_u',
        'name'   => 'PayU Service',
        'info'   => 'Settings for PayU service used in Class Ranker module',
        'sort'   => 1,
        'fields' => [
            [
                'name'    => 'status',
                'title'   => 'Status',
                'type'    => 'boolean',
                'default' => true,
            ], [
                'name'    => 'production_mode',
                'title'   => 'Production Mode',
                'type'    => 'boolean',
                'default' => true,
            ], [
                'name'       => 'key',
                'title'      => 'PayU Key',
                'type'       => 'text',
            ], [
                'name'       => 'salt',
                'title'      => 'PayU Salt',
                'type'       => 'text',
            ], [
                'name'    => 'callback_url',
                'title'   => 'Callback URL',
                'type'    => 'text',
            ],
        ],
    ], [
        'key'    => 'class_ranker.settings.openai',
        'name'   => 'OpenAI Service',
        'info'   => 'Settings for OpenAI service used in Class Ranker module',
        'sort'   => 1,
        'fields' => [
            [
                'name'    => 'status',
                'title'   => 'Status',
                'type'    => 'boolean',
            ], [
                'name'  => 'key',
                'title' => 'API Key',
                'type'  => 'text',
            ], [
                'name'  => 'project_id',
                'title' => 'Project ID',
                'type'  => 'text',
            ],
        ],
    ], [
        'key'    => 'class_ranker.settings.app_version',
        'name'   => 'APP Version',
        'info'   => 'Settings for managing app version in Class Ranker module',
        'sort'   => 1,
        'fields' => [
            [
                'name'       => 'latest_version',
                'title'      => 'Latest Version',
                'type'       => 'number',
            ], [
                'name'       => 'min_version',
                'title'      => 'Supported Version',
                'type'       => 'number',
            ], [
                'name'       => 'store_url',
                'title'      => 'Store URL',
                'type'       => 'text',
            ],
        ],
    ], [
        'key'    => 'class_ranker.settings.firebase',
        'name'   => 'Firebase Service',
        'info'   => 'Settings for Firebase service used in Class Ranker module',
        'sort'   => 1,
        'fields' => [
            [
                'name'       => 'project_id',
                'title'      => 'Project ID',
                'type'       => 'text',
            ], [
                'name'       => 'firebase_service',
                'title'      => 'Firebase Service',
                'type'       => 'text',
            ],
        ],
    ],
];
