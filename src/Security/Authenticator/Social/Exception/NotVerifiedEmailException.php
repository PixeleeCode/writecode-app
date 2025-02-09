<?php

namespace App\Security\Authenticator\Social\Exception;

use Symfony\Component\Security\Core\Exception\CustomUserMessageAuthenticationException;
use Throwable;

class NotVerifiedEmailException extends CustomUserMessageAuthenticationException
{
    public function __construct(
        string $message = 'Ce compte ne semble pas posséder d\'adresse email vérifiée',
        array $messageData = [],
        int $code = 0,
        Throwable $previous = null
    ) {
        parent::__construct($message, $messageData, $code, $previous);
    }
}
