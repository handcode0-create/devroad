<?php

namespace App\Services\Ai;

use RuntimeException;

/** Erreur d'IA dont le message peut être montré tel quel à l'utilisateur. */
class AiException extends RuntimeException
{
}
