<?php

namespace App\MessageHandler;

use App\Message\Typesense;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;

final class TypesenseHandler implements MessageHandlerInterface
{
    public function __invoke(Typesense $typesense): bool
    {
        if ($typesense->getRecreate()) {
            exec('php '.__DIR__.'/../../../bin/console typesense:create');
        }

        // J'aime pas le "exec()", mais moi être pressé
        exec('php '.__DIR__.'/../../../bin/console typesense:populate');

        return true;
    }
}
