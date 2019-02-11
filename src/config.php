<?php

return [
    // Configuration for transaction handling.
    'transaction' => [
        // Specifies whether decoration of events, bus & mailer service is enabled.
        'enable' => env('LQEXT_TRANSACTION_ENABLE', false),
        // Whitelisted events, commands, mailable names which are to be transaction aware.
        // Alternatively a class can use TransactionAware trait.
        'whitelist' => [
        ],
    ],
    // Configuration for queue push error handling.
    'queue' => [
        // Specifies whether to enable queue push error handling.
        'enable' => env('LQEXT_QUEUE_ENABLE', false),
        // Specifies storage driver to use to write/read queue messages which fails to push to original target.
        // 'storage' => env('LQEXT_QUEUE_STORAGE', 'file'),
    ],
];
