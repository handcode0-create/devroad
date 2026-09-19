<?php

namespace App\Services;

use Symfony\Component\Process\Exception\ProcessTimedOutException;
use Throwable;
use Symfony\Component\Process\Process;

class CodeRunnerService
{
    private const TIMEOUT_SECONDS = 12;

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
            return [
                'status' => 'unsupported',
                'stdout' => '',
                'stderr' => 'Langage non pris en charge par le sandbox DevRoad.',
                'exit_code' => null,
                'duration_ms' => 0,
            ];
        }

        if (trim($code) === '') {
            return [
                'status' => 'error',
                'stdout' => '',
                'stderr' => 'Le code est vide.',
                'exit_code' => null,
                'duration_ms' => 0,
            ];
        }

        $process = new Process([
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
        ]);

        $startedAt = microtime(true);

        try {
            $process->setTimeout(self::TIMEOUT_SECONDS);
            $process->setInput($code);
            $process->run();
        } catch (ProcessTimedOutException) {
            return [
                'status' => 'timeout',
                'stdout' => $this->limitOutput($process->getOutput()),
                'stderr' => 'Temps d’exécution dépassé (12 secondes).',
                'exit_code' => $process->getExitCode(),
                'duration_ms' => (int) round((microtime(true) - $startedAt) * 1000),
            ];
        }
        catch (Throwable $exception) {
            return [
                'status' => 'unavailable',
                'stdout' => '',
                'stderr' => 'Sandbox indisponible : ' . $exception->getMessage(),
                'exit_code' => null,
                'duration_ms' => (int) round((microtime(true) - $startedAt) * 1000),
            ];
        }

        return [
            'status' => $process->isSuccessful() ? 'success' : 'error',
            'stdout' => $this->limitOutput($process->getOutput()),
            'stderr' => $this->limitOutput($process->getErrorOutput()),
            'exit_code' => $process->getExitCode(),
            'duration_ms' => (int) round((microtime(true) - $startedAt) * 1000),
        ];
    }

    private function limitOutput(string $output): string
    {
        $output = trim($output);
        $limit = 20000;

        if (mb_strlen($output) <= $limit) {
            return $output;
        }

        return mb_substr($output, 0, $limit) . "\n… sortie tronquée par DevRoad.";
    }
}
