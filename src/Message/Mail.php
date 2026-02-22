<?php

namespace App\Message;

use Symfony\Component\Messenger\Attribute\AsMessage;

#[AsMessage('async')]
class Mail
{
    public function __construct(
        public readonly string $type,
        public readonly string $recipientEmail,
        public readonly string $subject,
        public readonly string $template,
        public readonly array $context = [],
    ) {}
}
