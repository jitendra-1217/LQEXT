<?php

return [

    // Configuration for making events, bus dispatcher and mailer services
    // transaction aware.
    'transaction' => [

        // Specifies whether to enable this extension.
        'enable' => env('LQEXT_ENABLE_TXN_AWARE', false),

        // This is the number of transactions to skip in testing mode
        // This has been added since tests are wrapped in a tnx which
        // is rolled back at the end of the test, therefore events were
        // not getting fired. Using this we can skip the given number of
        // transactions. This has been kept as configurable because there
        // can be more than 1 database which is being used
        'testing_txn_skip_count' => env('LQEXT_TESTING_TXN_SKIP_COUNT', 1),

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
