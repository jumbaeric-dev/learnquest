<?php

namespace App\Data\Child\Dashboard;

use App\Data\DTO;

final readonly class WelcomeDTO extends DTO
{
    public function __construct(
        public string $greeting,
        public string $headline,
        public string $message,
        public string $buttonText,
        public string $buttonAction,
    ) {
    }

}