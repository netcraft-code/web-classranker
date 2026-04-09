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
    ],
];
