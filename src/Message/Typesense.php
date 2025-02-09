<?php

namespace App\Message;

final class Typesense
{
    private bool $recreate;

    public function __construct(bool $recreate = false)
    {
        $this->recreate = $recreate;
    }

    public function getRecreate(): bool
    {
        return $this->recreate;
    }
}
