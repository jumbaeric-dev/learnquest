<?php

namespace App\Data\Child\Dashboard;

use App\Data\DTO;

final readonly class NovaWidgetDTO extends DTO
{
    public function __construct(
        public string $greeting,
        public string $message,
        public string $buttonText,
        public string $action,
        public string $icon,
    ) {
    }

}