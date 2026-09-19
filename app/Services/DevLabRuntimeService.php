<?php

namespace App\Services;

use Illuminate\Support\Facades\File;
use Symfony\Component\Process\Exception\ProcessTimedOutException;
use Symfony\Component\Process\Process;
use Throwable;

class DevLabRuntimeService
{
    private const TIMEOUT_SECONDS = 20;
    private const LARAVEL_SETUP_TIMEOUT_SECONDS = 180;

    public function run(
        string $runtime,
        int $userId,
        int $workspaceId,
        array $files,
        string $entryFile,
        string $command
    ): array {
        if (! app()->isLocal() || ! config('devroad.devlab.allow_host_runtime', true)) {
            return $this->result(
                'unavailable',
                'Le runtime local est disponible uniquement en environnement de développement.'
            );
        }

        $workspace = config('devroad.devlab.root', storage_path('app/devlab'))
            .DIRECTORY_SEPARATOR
            .'u'.$userId
            .DIRECTORY_SEPARATOR
            .'w'.$workspaceId;

        File::ensureDirectoryExists($workspace);

        try {
            if ($runtime === 'laravel') {
                $setup = $this->ensureLaravelWorkspace($workspace);

                if ($setup !== null) {
                    return $setup;
                }
            }

            $this->writeFiles($workspace, $files);

            return match ($runtime) {
                'node' => $this->runNode($workspace, $command, $entryFile),
                'php' => $this->runPhp($workspace, $command, $entryFile),
                'laravel' => $this->runLaravel($workspace, $command),
                'python' => $this->runPython($workspace, $command, $entryFile),
                default => $this->result(
                    'unsupported',
                    'Runtime non pris en charge par DevRoad.'
                ),
            };
        } catch (Throwable $exception) {
            return $this->result(
                'error',
                'Impossible de préparer le workspace : '.$exception->getMessage()
            );
        }
    }

    private function runNode(
        string $workspace,
        string $command,
        string $entryFile
    ): array {
        $tokens = $this->parseNodeCommand($command, $entryFile);

        if ($tokens === null) {
            return $this->result('rejected', 'Commande Node.js non autorisée.');
        }

        return $this->execute($tokens, $workspace, self::TIMEOUT_SECONDS);
    }

    private function runPhp(
        string $workspace,
        string $command,
        string $entryFile
    ): array {
        $tokens = $this->parsePhpCommand($command, $entryFile);

        if ($tokens === null) {
            return $this->result('rejected', 'Commande PHP non autorisée.');
        }

        return $this->execute($tokens, $workspace, self::TIMEOUT_SECONDS);
    }

    private function runPython(
        string $workspace,
        string $command,
        string $entryFile
    ): array {
        $tokens = $this->parsePythonCommand($command, $entryFile);

        if ($tokens === null) {
            return $this->result('rejected', 'Commande Python non autorisée.');
        }

        return $this->execute($tokens, $workspace, self::TIMEOUT_SECONDS);
    }

    private function ensureLaravelWorkspace(string $workspace): ?array
    {
        if (File::exists($workspace.DIRECTORY_SEPARATOR.'artisan')) {
            return null;
        }

        $setup = $this->execute(
            [
                'composer',
                'create-project',
                'laravel/laravel:^12.0',
                '.',
                '--no-interaction',
                '--prefer-dist',
            ],
            $workspace,
            self::LARAVEL_SETUP_TIMEOUT_SECONDS
        );

        return $setup['status'] === 'success' ? null : $setup;
    }

    private function runLaravel(string $workspace, string $command): array
    {
        $tokens = $this->parseLaravelCommand($command);

        if ($tokens === null) {
            return $this->result(
                'rejected',
                'Commande Laravel non autorisée.'
            );
        }

        return $this->execute($tokens, $workspace, self::TIMEOUT_SECONDS);
    }

    private function writeFiles(string $workspace, array $files): void
    {
        foreach ($files as $file) {
            $path = $this->safeRelativePath($file['path'] ?? null);
            $content = $file['content'] ?? '';

            if ($path === null) {
                throw new \InvalidArgumentException('Chemin de fichier invalide.');
            }

            $fullPath = $workspace.DIRECTORY_SEPARATOR.str_replace('/', DIRECTORY_SEPARATOR, $path);

            File::ensureDirectoryExists(dirname($fullPath));
            File::put($fullPath, $content);
        }
    }

    private function safeRelativePath(?string $path): ?string
    {
        if (! is_string($path) || $path === '') {
            return null;
        }

        $path = trim(str_replace('\\', '/', $path), '/');

        if (
            $path === ''
            || basename($path) === '.env'
            || str_contains($path, '..')
            || preg_match('/^[A-Za-z]:/', $path)
            || preg_match('/[^A-Za-z0-9_\/\.\-]/', $path)
        ) {
            return null;
        }

        if (preg_match('#^(?:\.git|vendor|node_modules|storage)/#', $path)) {
            return null;
        }

        return $path;
    }

    private function parseNodeCommand(string $command, string $entryFile): ?array
    {
        $command = trim($command);
        $entry = $this->safeRelativePath($entryFile);

        if ($entry === null) {
            return null;
        }

        if ($command === '' || $command === 'run') {
            return ['node', $entry];
        }

        if (preg_match('/^node\s+([A-Za-z0-9_\/\.\-]+)$/', $command, $matches)) {
            return ['node', $matches[1]];
        }

        if ($command === 'node --version') {
            return ['node', '--version'];
        }

        if ($command === 'npm --version') {
            return ['npm', '--version'];
        }

        if ($command === 'npm install') {
            return ['npm', 'install'];
        }

        if (preg_match('/^npm\s+(run|test)\s+([A-Za-z0-9_:\-.]+)$/', $command, $matches)) {
            return ['npm', $matches[1], $matches[2]];
        }

        return null;
    }

    private function parsePhpCommand(string $command, string $entryFile): ?array
    {
        $command = trim($command);
        $entry = $this->safeRelativePath($entryFile);

        if ($entry === null) {
            return null;
        }

        if ($command === '' || $command === 'run') {
            return ['php', $entry];
        }

        if (preg_match('/^php\s+([A-Za-z0-9_\/\.\-]+)$/', $command, $matches)) {
            return ['php', $matches[1]];
        }

        if ($command === 'php --version') {
            return ['php', '--version'];
        }

        return null;
    }

    private function parsePythonCommand(string $command, string $entryFile): ?array
    {
        $command = trim($command);
        $entry = $this->safeRelativePath($entryFile);

        if ($entry === null) {
            return null;
        }

        if ($command === '' || $command === 'run') {
            return ['python', $entry];
        }

        if (preg_match('/^python\s+([A-Za-z0-9_\/\.\-]+)$/', $command, $matches)) {
            return ['python', $matches[1]];
        }

        if ($command === 'python --version') {
            return ['python', '--version'];
        }

        return null;
    }

    private function parseLaravelCommand(string $command): ?array
    {
        $command = trim($command);

        if ($command === '') {
            return ['php', 'artisan', 'route:list'];
        }

        if ($command === 'php artisan --version') {
            return ['php', 'artisan', '--version'];
        }

        if ($command === 'php artisan migrate') {
            return ['php', 'artisan', 'migrate'];
        }

        if ($command === 'php artisan test') {
            return ['php', 'artisan', 'test'];
        }

        if ($command === 'php artisan route:list') {
            return ['php', 'artisan', 'route:list'];
        }

        if (preg_match(
            '/^php artisan (make:(model|controller|request|migration|policy|test|factory|seeder)) ([A-Za-z0-9_\/\.\-]+)(.*)$/',
            $command,
            $matches
        )) {
            $suffix = trim($matches[4]);

            if ($suffix !== '' && ! preg_match('/^(?:-[A-Za-z0-9_-]+(?:\s+[A-Za-z0-9_.-]+)?)?(?:\s+-[A-Za-z0-9_-]+)*$/', $suffix)) {
                return null;
            }

            $tokens = [
                'php',
                'artisan',
                $matches[1],
                $matches[3],
            ];

            if ($suffix !== '') {
                $tokens = [...$tokens, ...preg_split('/\s+/', $suffix)];
            }

            return $tokens;
        }

        return null;
    }

    private function execute(
        array $command,
        string $workingDirectory,
        int $timeout
    ): array {
        $startedAt = microtime(true);
        $process = new Process($command, $workingDirectory);

        try {
            $process->setTimeout($timeout);
            $process->run();
        } catch (ProcessTimedOutException) {
            return [
                'status' => 'timeout',
                'stdout' => $this->limitOutput($process->getOutput()),
                'stderr' => 'Temps d’exécution dépassé.',
                'exit_code' => $process->getExitCode(),
                'duration_ms' => $this->duration($startedAt),
            ];
        } catch (Throwable $exception) {
            return [
                'status' => 'unavailable',
                'stdout' => '',
                'stderr' => $exception->getMessage(),
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

        return mb_substr($output, 0, $limit)."
… sortie tronquée par DevRoad.";
    }
}
