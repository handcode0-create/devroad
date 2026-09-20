<?php

return [
    'enabled' => filter_var(env('DEVROAD_SANDBOX_ENABLED', false), FILTER_VALIDATE_BOOL),
    'driver' => env('DEVROAD_SANDBOX_DRIVER', 'unavailable'),
    'default_region' => env('DEVROAD_SANDBOX_REGION', 'us'),
    'api_url' => rtrim(env('DAYTONA_API_URL', 'https://app.daytona.io/api'), '/'),
    'toolbox_url' => rtrim(env('DAYTONA_TOOLBOX_URL', 'https://proxy.app.daytona.io/toolbox'), '/'),
    'api_key' => env('DAYTONA_API_KEY'),

    'resources' => [
        'cpu' => (int) env('DEVROAD_SANDBOX_CPU', 1),
        'memory' => (int) env('DEVROAD_SANDBOX_MEMORY_GB', 2),
        'disk' => (int) env('DEVROAD_SANDBOX_DISK_GB', 5),
    ],

    'templates' => [
        'react' => [
            'label' => 'React + Vite',
            'runtime' => 'node',
            'version' => '22',
            'image' => 'node:22-bookworm',
            'port' => 5173,
            'bootstrap' => 'npm create vite@latest . -- --template react && npm install',
            'serve' => 'npm run dev -- --host 0.0.0.0',
        ],
        'nextjs' => [
            'label' => 'Next.js',
            'runtime' => 'node',
            'version' => '22',
            'image' => 'node:22-bookworm',
            'port' => 3000,
            'bootstrap' => 'npx create-next-app@latest . --js --tailwind --eslint --app --src-dir --use-npm --import-alias "@/*" --no-git',
            'serve' => 'npm run dev -- --hostname 0.0.0.0',
        ],
        'node' => [
            'label' => 'Node.js',
            'runtime' => 'node',
            'version' => '22',
            'image' => 'node:22-bookworm',
            'port' => 3000,
            'bootstrap' => 'npm init -y && printf \'console.log("DevRoad Node.js Sandbox");\\n\' > index.js && npm pkg set scripts.start="node index.js"',
            'serve' => 'npm start',
        ],
        'php' => [
            'label' => 'PHP',
            'runtime' => 'php',
            'version' => '8.3',
            'image' => 'php:8.3-cli-bookworm',
            'port' => 8000,
            'bootstrap' => 'mkdir -p public && printf \'<?php\\nheader("Content-Type: text/html; charset=UTF-8");\\necho "<h1>DevRoad PHP Sandbox</h1>";\\n\' > public/index.php',
            'serve' => 'php -S 0.0.0.0:8000 -t public',
        ],
        'laravel' => [
            'label' => 'Laravel',
            'runtime' => 'php',
            'version' => '8.3',
            'image' => 'serversideup/php:8.3-cli',
            'port' => 8000,
            'bootstrap' => 'composer create-project laravel/laravel .',
            'serve' => 'php artisan serve --host=0.0.0.0 --port=8000',
        ],
    ],
];
