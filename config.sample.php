<?php
return [
    'site' => [
        'name' => 'Auto Sharm',
        'url' => 'http://localhost:8110',
        'email' => 'info@autosharm.com',
        'phone' => '+20 100 000 0000',
        'whatsapp' => '+20 100 000 0000',
        'currency' => 'EUR',
        'timezone' => 'Africa/Cairo',
        'address' => 'Naama Bay, Sharm El Sheikh, Egypt',
    ],
    // Per dev locale: SQLite (zero setup). Su Hostinger commentare/sostituire
    // con il blocco mysql qui sotto.
    'db' => [
        'driver' => 'sqlite',
        'path'   => __DIR__ . '/config-storage/auto-sharm.db',
    ],
    // 'db' => [
    //     'driver' => 'mysql',
    //     'host' => 'localhost',
    //     'name' => 'u749757264_auto_sharm',
    //     'user' => 'u749757264_auto_sharm',
    //     'pass' => 'CHANGE_ME',
    //     'charset' => 'utf8mb4',
    // ],
    'admin_default' => [
        'email' => 'admin@autosharm.com',
        'password' => 'admin123',
        'name' => 'Admin Auto Sharm',
    ],
];
