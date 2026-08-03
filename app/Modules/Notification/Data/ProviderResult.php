<?php

namespace App\Modules\Notification\Data;

readonly class ProviderResult
{
    public function __construct(
        public ?string $messageId = null,
    ) {}
}
