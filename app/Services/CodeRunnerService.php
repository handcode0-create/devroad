<?php

namespace App\Services;

use Symfony\Component\Process\Exception\ProcessTimedOutException;
use Symfony\Component\Process\Process;
use Throwable;

class CodeRunnerService
{
    private const TIMEOUT_SECONDS = 12;
    private const LARAVEL_IMAGE = 'devroad/laravel-sandbox:latest';

    public function run(string $language, string $code): array
    {
        $profile = match ($language) {
            'php' => [
                'image' => 'php:8.2-cli-alpine',
                'command' => ['php'],
            ],
            'javascript' => [
                'image' => 'node:22-alpine',
                'command' => ['node', '-'],
            ],
            default => null,
        };

        if ($profile === null) {
            return $this->result('unsupported', 'Langage non pris en charge par le sandbox DevRoad.');
        }

        if (trim($code) === '') {
            return $this->result('error', 'Le code est vide.');
        }

        return $this->runDocker(
            [
                'docker',
                'run',
                '--rm',
                '-i',
                '--network',
                'none',
                '--memory',
                '128m',
                '--cpus',
                '0.5',
                '--pids-limit',
                '64',
                '--read-only',
                '--tmpfs',
                '/tmp:rw,nosuid,nodev,size=16m',
                '--cap-drop',
                'ALL',
                '--security-opt',
                'no-new-privileges',
                $profile['image'],
                ...$profile['command'],
            ],
            $code,
        );
    }

    public function runLaravel(
        int $userId,
        int $stepId,
        string $command,
        ?string $filePath = null,
        ?string $fileContents = null
    ): array {
        $tokens = $this->parseLaravelCommand($command);

        if ($tokens === null) {
            return $this->result(
                'rejected',
                'Commande non autorisée dans le terminal pédagogique.'
            );
        }

        $volume = $this->volumeName($userId, $stepId);
        $script = '';

        if ($filePath !== null && $fileContents !== null) {
            $encoded = base64_encode($fileContents);
            $target = '/workspace/' . ltrim($filePath, '/');

            $script .= 'printf %s ' . escapeshellarg($encoded)
                . ' | base64 -d > ' . escapeshellarg($target) . PHP_EOL;
        }

        $script .= 'exec ' . implode(
            ' ',
            array_map('escapeshellarg', $tokens)
        );

        return $this->runDocker(
            [
                'docker',
                'run',
                '--rm',
                '-i',
                '--network',
                'none',
                '--memory',
                '512m',
                '--cpus',
                '1',
                '--pids-limit',
                '128',
                '--read-only',
                '--tmpfs',
                '/tmp:rw,nosuid,nodev,size=32m',
                '--cap-drop',
                'ALL',
                '--security-opt',
                'no-new-privileges',
                '--mount',
                'type=volume,src=' . $volume . ',dst=/workspace',
                self::LARAVEL_IMAGE,
                'sh',
                '-lc',
                $script,
            ],
            null,
        );
    }

    private function parseLaravelCommand(string $command): ?array
    {
        $command = trim($command);

        if ($command === '') {
            return ['php', 'artisan', 'route:list'];
        }

        if ($command === 'pwd') {
            return ['pwd'];
        }

        if ($command === 'ls' || preg_match('/^ls\s+[A-Za-z0-9_\/.\-]+$/', $command)) {
            return preg_split('/\s+/', $command);
        }

        if (preg_match('/^cat\s+([A-Za-z0-9_\/\.\-]+)$/', $command, $matches)) {
            return ['cat', $matches[1]];
        }

        if ($command === 'composer show') {
            return ['composer', 'show'];
        }

        if ($command === 'composer validate') {
            return ['composer', 'validate'];
        }

        if ($command === 'composer dump-autoload') {
            return ['composer', 'dump-autoload'];
        }

        if ($command === 'npm run build') {
            return ['npm', 'run', 'build'];
        }

        if (preg_match('/^php artisan (.+)$/', $command, $matches)) {
            $parts = preg_split('/\s+/', trim($matches[1]));
            $action = $parts[0] ?? '';

            $simpleCommands = [
                'about',
                'list',
                'route:list',
                'route:clear',
                'migrate',
                'migrate:fresh',
                'db:seed',
                'optimize:clear',
                'config:clear',
                'cache:clear',
                'view:clear',
                'test',
            ];

            if (in_array($action, $simpleCommands, true)) {
                return ['php', 'artisan', ...$parts];
            }

            $makerCommands = [
                'make:model',
                'make:controller',
                'make:migration',
                'make:request',
                'make:policy',
                'make:test',
                'make:factory',
                'make:seeder',
            ];

            if (
                in_array($action, $makerCommands, true)
                && isset($parts[1])
                && preg_match('/^[A-Za-z0-9_\/\.\-]+$/', $parts[1])
            ) {
                return ['php', 'artisan', ...$parts];
            }
        }

        return null;
    }

    private function runDocker(array $arguments, ?string $input): array
    {
        $startedAt = microtime(true);
        $process = new Process($arguments);

        try {
            $process->setTimeout(self::TIMEOUT_SECONDS);
            $process->setInput($input);
            $process->run();
        } catch (ProcessTimedOutException) {
            return [
                'status' => 'timeout',
                'stdout' => $this->limitOutput($process->getOutput()),
                'stderr' => 'Temps d’exécution dépassé (12 secondes).',
                'exit_code' => $process->getExitCode(),
                'duration_ms' => $this->duration($startedAt),
            ];
        } catch (Throwable $exception) {
            return [
                'status' => 'unavailable',
                'stdout' => '',
                'stderr' => 'Sandbox indisponible : ' . $exception->getMessage(),
                'exit_code' => null,
                'duration_ms' => $this->duration($startedAt),
            ];
        }

        return [
            'status' => $process->isSuccessful() ? 'success' : 'error',
            'stdout' => $this->limitOutput($process->getOutput()),
            'stderr' => $this->limitOutput($process->getErrorOutput()),
            'exit_code' => $process->getExitCode(),
            'duration_ms' => $this->duration($startedAt),
        ];
    }

    private function volumeName(int $userId, int $stepId): string
    {
        return 'devroad_sandbox_u' . $userId . '_s' . $stepId;
    }

    private function result(string $status, string $stderr): array
    {
        return [
            'status' => $status,
            'stdout' => '',
            'stderr' => $stderr,
            'exit_code' => null,
            'duration_ms' => 0,
        ];
    }

    private function duration(float $startedAt): int
    {
        return (int) round((microtime(true) - $startedAt) * 1000);
    }

    private function limitOutput(string $output): string
    {
        $output = trim($output);
        $limit = 20000;

        if (mb_strlen($output) <= $limit) {
            return $output;
        }

        return mb_substr($output, 0, $limit)
            . "\n… sortie tronquée par DevRoad.";
    }
}
