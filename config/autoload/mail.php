<?php

declare(strict_types=1);

return [
    'host' => \Hyperf\Support\env('MAIL_HOST', 'localhost'),
    'port' => (int) \Hyperf\Support\env('MAIL_PORT', 1025),
    'from' => [
        'address' => \Hyperf\Support\env('MAIL_FROM_ADDRESS', 'noreply@withdraw-pix.com'),
        'name' => \Hyperf\Support\env('MAIL_FROM_NAME', 'Withdraw PIX'),
    ],
];
