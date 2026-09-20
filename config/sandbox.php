<?php

return [
    'enabled' => (bool) env('DEVROAD_SANDBOX_ENABLED', false),
    'driver' => env('DEVROAD_SANDBOX_DRIVER', 'unavailable'),
    'default_region' => env('DEVROAD_SANDBOX_REGION', 'auto'),

    'templates' => [
        'react' => [
            'label' => 'React + Vite',
            'runtime' => 'node',
            'version' => '22',
        ],
        'nextjs' => [
            'label' => 'Next.js',
            'runtime' => 'node',
            'version' => '22',
        ],
        'node' => [
            'label' => 'Node.js',
            'runtime' => 'node',
            'version' => '22',
        ],
        'php' => [
            'label' => 'PHP',
            'runtime' => 'php',
            'version' => '8.3',
        ],
        'laravel' => [
            'label' => 'Laravel',
            'runtime' => 'php',
            'version' => '8.3',
        ],
    ],
];
