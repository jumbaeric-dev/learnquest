<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\Blade;
use Tests\TestCase;

class ContinueJourneyComponentTest extends TestCase
{
    public function test_continue_journey_component_renders_supplied_journey_data(): void
    {
        $journey = [
            'course' => 'Space Explorers',
            'lesson' => 'The Solar System',
            'progress' => 25,
            'buttonText' => 'Continue',
            'buttonAction' => 'continue',
        ];

        $html = Blade::render(
            '<x-child.worlds.continue-journey :journey="$journey" />',
            ['journey' => $journey]
        );

        $this->assertStringContainsString(
            'Space Explorers',
            $html
        );

        $this->assertStringContainsString(
            'The Solar System',
            $html
        );

        $this->assertStringContainsString(
            'width: 25%;',
            $html
        );

        $this->assertStringContainsString(
            'Continue',
            $html
        );
    }
}
