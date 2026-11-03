<?php

namespace Tests\Feature;

use App\Models\Child;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ChildActiveNavigationTest extends TestCase
{
    use RefreshDatabase;

    public function test_explorer_route_marks_explorer_navigation_item_active(): void
    {
        $child = Child::factory()->create();

        $response = $this->actingAs($child->user)
            ->get(route('child.explore'));

        $response->assertOk();

        $html = $response->getContent();
        $this->assertActiveNavigationLink(
            $html,
            route('child.explore'),
            'Explorer'
        );
        $this->assertInactiveNavigationLink(
            $html,
            route('child.dashboard'),
            'My Universe'
        );
    }

    public function test_worlds_shortcut_marks_worlds_active_and_targets_world_section(): void
    {
        $child = Child::factory()->create();
        $worldsUrl = route('child.explore', ['section' => 'worlds']);

        $response = $this->actingAs($child->user)->get($worldsUrl);

        $response->assertOk()
            ->assertSee('id="learning-worlds"', false);

        $this->assertActiveNavigationLink(
            $response->getContent(),
            $worldsUrl . '#learning-worlds',
            'Worlds'
        );
    }

    private function assertActiveNavigationLink(
        string $html,
        string $href,
        string $label,
    ): void {
        $href = preg_quote($href, '~');
        $label = preg_quote($label, '~');

        $this->assertMatchesRegularExpression(
            '~<a\b(?=[^>]*\bhref="' . $href . '")(?=[^>]*\bclass="lq-nav-link is-active")[^>]*>.*?' . $label . '.*?</a>~s',
            $html
        );
    }

    private function assertInactiveNavigationLink(
        string $html,
        string $href,
        string $label,
    ): void {
        $href = preg_quote($href, '~');
        $label = preg_quote($label, '~');

        $this->assertDoesNotMatchRegularExpression(
            '~<a\b(?=[^>]*\bhref="' . $href . '")(?=[^>]*\bclass="lq-nav-link is-active")[^>]*>.*?' . $label . '.*?</a>~s',
            $html
        );
    }
}
