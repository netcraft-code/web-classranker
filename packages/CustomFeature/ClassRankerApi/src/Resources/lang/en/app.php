<?php

return [
    'shop' => [
        'customer' => [
            'accounts' => [
                'create-success'     => 'Account created successfully.',
                'delete-success'     => 'Account deleted successfully.',
                'logged-in-success'  => 'Logged in successfully.',
                'logged-out-success' => 'Logged out successfully.',
                'update-success'     => 'Account updated successfully.',

                'error' => [
                    'credential-error'  => 'Credentials are incorrect.',
                    'invalid'           => 'Invalid Email or Password',
                    'password-mismatch' => 'Current password does not match.',
                    'update-failed'     => 'An error has occurred while updating your account',
                ],
            ],
        ],

        'errors' => [
            '404' => [
                'message' => 'Oops! The page you\'re looking for is on vacation. It seems we couldn\'t find what you were searching for.',
                'title'   => '404 Page Not Found',
            ],

            '401' => [
                'message' => 'Oops! Looks like you\'re not allowed to access this page. It seems you\'re missing the necessary credentials.',
                'title'   => '401 Unauthorized',
            ],

            '403' => [
                'message' => 'Oops! This page is off-limits. It appears you don\'t have the required permissions to view this content.',
                'title'   => '403 Forbidden',
            ],

            '500' => [
                'message' => 'Oops! Something went wrong. It seems we\'re having trouble loading the page you\'re looking for.',
                'title'   => '500 Internal Server Error',
            ],

            '503' => [
                'message' => 'Oops! Looks like we\'re temporarily down for maintenance. Please check back in a bit.',
                'title'   => '503 Service Unavailable',
            ],
        ],
    ],
];