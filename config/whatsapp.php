<?php

return [
    // Bot credentials live ONLY in .env (WA_BOT_URL, WA_BOT_KEY). Never hard-code them.
    'bot' => [
        'url' => env('WA_BOT_URL'),
        'key' => env('WA_BOT_KEY'),
        'timeout' => (int) env('WA_BOT_TIMEOUT', 10),
    ],
    'window' => ['start' => 8, 'end' => 20],   // automatic messages only between 08:00 and 20:00 (Asia/Jakarta)
    'max_per_run' => 10,                       // at most this many messages per scheduler run
    'gap_seconds' => [3, 6],                   // random pause between two messages
];
