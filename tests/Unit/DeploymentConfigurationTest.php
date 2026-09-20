<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;

class DeploymentConfigurationTest extends TestCase
{
    public function test_dockerfile_cmd_is_valid_json_and_uses_the_railway_port(): void
    {
        $dockerfile = file_get_contents(dirname(__DIR__, 2).'/Dockerfile');

        $this->assertIsString($dockerfile);

        preg_match_all('/^CMD\s+(.+)$/m', $dockerfile, $matches);

        $this->assertNotEmpty($matches[1], 'Dockerfile must contain a JSON CMD instruction.');

        $cmd = trim(end($matches[1]));
        $decoded = json_decode($cmd, true);

        $this->assertSame(JSON_ERROR_NONE, json_last_error());
        $this->assertIsArray($decoded);
        $this->assertSame('sh', $decoded[0] ?? null);
        $this->assertSame('-c', $decoded[1] ?? null);

        $command = $decoded[2] ?? '';

        $this->assertStringContainsString('${PORT:-8080}', $command);
        $this->assertStringNotContainsString('\\${PORT:-8080}', $command);
    }

    public function test_railway_json_uses_dockerfile_and_up_healthcheck(): void
    {
        $contents = file_get_contents(dirname(__DIR__, 2).'/railway.json');

        $this->assertIsString($contents);

        $config = json_decode($contents, true);

        $this->assertSame(JSON_ERROR_NONE, json_last_error());
        $this->assertIsArray($config);
        $this->assertSame('DOCKERFILE', $config['build']['builder'] ?? null);
        $this->assertSame('Dockerfile', $config['build']['dockerfilePath'] ?? null);
        $this->assertSame('/up', $config['deploy']['healthcheckPath'] ?? null);
    }
}
