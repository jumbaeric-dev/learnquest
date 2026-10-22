<?php

namespace Tests\Feature;

use App\Livewire\Child\Settings\Index;
use App\Models\Child;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class ChildSettingsTest extends TestCase
{
    use RefreshDatabase;

    public function test_child_can_open_settings_page(): void
    {
        $child = Child::factory()->create();

        $this->actingAs($child->user)
            ->get(route('child.settings'))
            ->assertOk()
            ->assertSee('Learning Experience')
            ->assertSee('Sound effects')
            ->assertSee('Reduce animations')
            ->assertSee('Save Settings');
    }

    public function test_child_can_save_settings(): void
    {
        $child = Child::factory()->create([
            'settings' => [
                'sound_effects' => true,
                'reduced_animations' => false,
            ],
        ]);

        $this->actingAs($child->user);

        Livewire::test(Index::class)
            ->set('soundEffects', false)
            ->set('reducedAnimations', true)
            ->call('save')
            ->assertHasNoErrors()
            ->assertSee('Your settings have been saved!');

        $this->assertSame(
            false,
            $child->fresh()->settings['sound_effects']
        );

        $this->assertSame(
            true,
            $child->fresh()->settings['reduced_animations']
        );
    }

    public function test_settings_are_loaded_from_the_database(): void
    {
        $child = Child::factory()->create([
            'settings' => [
                'sound_effects' => false,
                'reduced_animations' => true,
            ],
        ]);

        $this->actingAs($child->user);

        Livewire::test(Index::class)
            ->assertSet('soundEffects', false)
            ->assertSet('reducedAnimations', true);
    }

    public function test_guest_cannot_access_settings(): void
    {
        $this->get(route('child.settings'))
            ->assertRedirect(route('login'));
    }
}
