<?php

namespace App\Data\Child\Dashboard;

use App\Data\DTO;

final readonly class CommunityPreviewDTO extends DTO
{
    public function __construct(
        public string $title,
        public string $message,
        public array $features,
        public string $buttonText,
    ) {
    }

}