<?php

namespace App\Support\Whatsapp;

final class SendResult
{
    public function __construct(public readonly bool $ok, public readonly ?string $error = null)
    {
    }
}
