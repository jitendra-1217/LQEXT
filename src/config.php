<?php

return [

    // Configuration for making events, bus dispatcher and mailer services
    // transaction aware.
    'transaction' => [

        // Specifies whether to enable this extension.
        'enable' => env('LQEXT_ENABLE_TXN_AWARE', false),

        // Whitelisted events, commands, mailable names which are to be
        // transaction aware.
        // Alternatively a class can use TransactionAware trait.
        'whitelist' => [
        ],
    ],

    /**
     * @see readme.md Unused configurations for now.
     */
    // Configuration for handling failures during queue push to remove services.
    'queue' => [

        // Specifies whether to enable this extension.
        'enable' => env('LQEXT_ENABLE_QUEUE_FAILURE_HANDLING', false),

        // Redis storage driver which is only implementation for now is used to
        // write/read queue messages which fails to push to original target.
        'redis' => [

            // Laravel's redis connection name.
            'connection' => env('LQEXT_REDIS_CONNECTION_NAME', 'default'),
        ],
    ],
];
