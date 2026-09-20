<?php

namespace App\Services;

use App\Models\DevLabProject;

class DevLabProjectTemplateService
{
    public function filesFor(string $template): array
    {
        return match ($template) {
            'html' => [
                ['path' => 'index.html', 'content' => "<!doctype html>\n<html lang=\"fr\">\n<head>\n    <meta charset=\"UTF-8\">\n    <meta name=\"viewport\" content=\"width=device-width, initial-scale=1.0\">\n    <title>DevRoad</title>\n    <link rel=\"stylesheet\" href=\"styles.css\">\n</head>\n<body>\n    <main>\n        <h1>Bonjour DevRoad</h1>\n        <p>Commence ton projet.</p>\n    </main>\n    <script src=\"main.js\"></script>\n</body>\n</html>\n"],
                ['path' => 'styles.css', 'content' => "body {\n    font-family: system-ui, sans-serif;\n    margin: 0;\n    padding: 2rem;\n}\n"],
                ['path' => 'main.js', 'content' => "console.log('Bonjour DevRoad');\n"],
            ],
            'node' => [['path' => 'main.js', 'content' => "console.log('Bonjour DevRoad');\n"]],
            'php' => [['path' => 'main.php', 'content' => "<?php\n\necho \"Bonjour DevRoad\";\n"]],
            'laravel' => [['path' => 'routes/web.php', 'content' => "<?php\n\nuse Illuminate\\Support\\Facades\\Route;\n\nRoute::get('/', function () {\n    return 'Bonjour DevRoad';\n});\n"]],
            default => throw new \InvalidArgumentException('Template DevLab invalide.'),
        };
    }

    public function runtimeFor(string $template): string
    {
        return in_array($template, ['node', 'php', 'laravel'], true) ? 'server' : 'browser';
    }

    public function normalizeTemplate(string $template): string
    {
        if (! in_array($template, DevLabProject::TEMPLATES, true)) {
            throw new \InvalidArgumentException('Template DevLab invalide.');
        }

        return $template;
    }
}
