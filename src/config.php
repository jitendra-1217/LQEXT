<?php

return [
    // Specifies whether decoration of events, bus & mailer service is enabled.
    'enable' => env('LQSTUFF_ENABLE', false),
    // Configuration for transaction handling.
    'transaction' => [
        // Whitelisted events, commands, mailable names.
        'whitelist' => [
        ],
    ],
];
