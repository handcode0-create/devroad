<?php

namespace App\Exceptions;

use RuntimeException;

class SandboxRuntimeUnavailable extends RuntimeException
{
    public function __construct()
    {
        parent::__construct(
            'Le runtime Sandbox n’est pas encore configuré sur cet environnement.'
        );
    }
}
