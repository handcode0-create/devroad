<?php

namespace App\Services\Sandbox;

use InvalidArgumentException;

class SandboxTemplateService
{
    public function all(): array
    {
        return config('sandbox.templates', []);
    }

    public function definition(string $template): array
    {
        $definition = $this->all()[$template] ?? null;

        if ($definition === null) {
            throw new InvalidArgumentException('Template Sandbox invalide.');
        }

        return $definition;
    }

    public function runtimeFor(string $template): string
    {
        return $this->definition($template)['runtime'];
    }

    public function versionFor(string $template): string
    {
        return $this->definition($template)['version'];
    }
}
