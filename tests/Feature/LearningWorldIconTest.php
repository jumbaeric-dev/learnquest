<?php

namespace Tests\Feature;

use App\Models\Subject;
use Database\Seeders\SubjectSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Blade;
use Tests\TestCase;

class LearningWorldIconTest extends TestCase
{
    use RefreshDatabase;

    public function test_heroicon_identifier_renders_as_svg_not_visible_text(): void
    {
        $html = Blade::render(
            '<x-lq.world-icon name="heroicon-o-cpu-chip" class="h-10 w-10" />'
        );

        $this->assertStringContainsString('<svg', $html);
        $this->assertStringNotContainsString('heroicon-o-cpu-chip', $html);
    }

    public function test_emoji_icon_is_rendered_as_text(): void
    {
        $html = Blade::render('<x-lq.world-icon name="🚀" />');

        $this->assertStringContainsString('🚀', $html);
    }

    public function test_missing_or_unknown_icon_identifier_uses_globe_fallback(): void
    {
        $missing = Blade::render('<x-lq.world-icon />');
        $unknown = Blade::render(
            '<x-lq.world-icon name="heroicon-o-not-a-real-icon" />'
        );

        $this->assertStringContainsString('🌍', $missing);
        $this->assertStringContainsString('🌍', $unknown);
        $this->assertStringNotContainsString(
            'heroicon-o-not-a-real-icon',
            $unknown
        );
    }

    public function test_world_tile_renders_database_heroicon_as_svg(): void
    {
        $html = Blade::render(
            '<x-lq.world-tile title="AI for Kids" icon="heroicon-o-cpu-chip" :show-action="false" />'
        );

        $this->assertStringContainsString('<svg', $html);
        $this->assertStringNotContainsString('heroicon-o-cpu-chip', $html);
        $this->assertStringContainsString('AI for Kids', $html);
    }

    public function test_subject_seeder_populates_missing_world_icons_and_preserves_custom_icons(): void
    {
        Subject::factory()->create([
            'name' => 'Custom Math Galaxy',
            'slug' => 'math-galaxy',
            'icon' => '🚀',
        ]);

        $this->seed(SubjectSeeder::class);

        $this->assertSame(
            '🚀',
            Subject::query()->where('slug', 'math-galaxy')->value('icon')
        );

        $this->assertSame(
            'heroicon-o-book-open',
            Subject::query()->where('slug', 'reading-kingdom')->value('icon')
        );

        $this->assertSame(
            'heroicon-o-sparkles',
            Subject::query()->where('slug', 'ai-academy')->value('icon')
        );
    }

    public function test_explore_featured_adventure_renders_database_heroicon_as_svg(): void
    {
        $html = view('livewire.child.explore.components.featured-adventure', [
            'adventure' => [
                'id' => 1,
                'icon' => 'heroicon-o-cpu-chip',
                'title' => 'AI for Kids',
                'description' => 'Prepare children for an AI-powered future.',
                'xp' => 180,
            ],
        ])->render();

        $this->assertStringContainsString('<svg', $html);
        $this->assertStringNotContainsString('heroicon-o-cpu-chip', $html);
    }

    public function test_mission_feature_and_card_render_database_heroicons_as_svg(): void
    {
        $mission = [
            'id' => 1,
            'icon' => 'heroicon-o-cpu-chip',
            'title' => 'AI for Kids',
            'category' => 'AI Academy',
            'description' => 'Prepare children for an AI-powered future.',
            'xp' => 180,
            'time' => '10 mins',
            'difficulty' => '10-14',
        ];

        $featuredHtml = view(
            'livewire.child.missions.components.featured-mission',
            ['mission' => $mission]
        )->render();

        $cardHtml = view(
            'livewire.child.missions.components.mission-card',
            ['mission' => $mission]
        )->render();

        foreach ([$featuredHtml, $cardHtml] as $html) {
            $this->assertStringContainsString('<svg', $html);
            $this->assertStringNotContainsString('heroicon-o-cpu-chip', $html);
        }
    }

    public function test_mission_category_filter_renders_database_heroicon_as_svg(): void
    {
        $html = view('livewire.child.missions.components.mission-categories', [
            'categories' => [[
                'name' => 'AI Academy',
                'label' => 'AI Academy',
                'icon' => 'heroicon-o-cpu-chip',
            ]],
            'selectedCategory' => 'all',
        ])->render();

        $this->assertStringContainsString('<svg', $html);
        $this->assertStringNotContainsString('heroicon-o-cpu-chip', $html);
    }

    public function test_world_adventure_path_renders_database_heroicon_as_svg(): void
    {
        $html = view('livewire.child.worlds.components.adventure-path', [
            'nodes' => [[
                'id' => 1,
                'title' => 'AI for Kids',
                'description' => 'Learn about AI.',
                'icon' => 'heroicon-o-cpu-chip',
                'status' => 'completed',
                'xp' => 180,
            ]],
        ])->render();

        $this->assertStringContainsString('<svg', $html);
        $this->assertStringNotContainsString('heroicon-o-cpu-chip', $html);
    }

}
