<?php

namespace Tests\Feature;

use App\Livewire\Child\Profile\Edit;
use App\Models\Child;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class ChildProfileEditTest extends TestCase
{
    use RefreshDatabase;

    public function test_child_can_open_the_edit_profile_page(): void
    {
        $child = Child::factory()->create();

        $this->actingAs($child->user)
            ->get(route('child.profile.edit'))
            ->assertOk()
            ->assertSee('Edit My Profile')
            ->assertSee('Choose your explorer avatar')
            ->assertSee('Save Changes');
    }

    public function test_child_can_update_their_editable_profile_fields(): void
    {
        $child = Child::factory()->create([
            'first_name' => 'Emma',
            'last_name' => 'Johnson',
            'avatar' => '🚀',
        ]);

        $this->actingAs($child->user);

        Livewire::test(Edit::class)
            ->set('first_name', 'Emma')
            ->set('last_name', 'Smith')
            ->set('date_of_birth', '2019-05-12')
            ->set('avatar', '🦊')
            ->call('save')
            ->assertHasNoErrors()
            ->assertRedirect(route('child.profile'));

        $child->refresh();

        $this->assertSame('Emma', $child->first_name);
        $this->assertSame('Smith', $child->last_name);
        $this->assertSame('2019-05-12', $child->date_of_birth->format('Y-m-d'));
        $this->assertSame('🦊', $child->avatar);
    }

    public function test_child_cannot_save_a_future_birthday(): void
    {
        $child = Child::factory()->create();

        $this->actingAs($child->user);

        Livewire::test(Edit::class)
            ->set('date_of_birth', now()->addDay()->toDateString())
            ->call('save')
            ->assertHasErrors(['date_of_birth']);

        $this->assertNotSame(
            now()->addDay()->toDateString(),
            $child->fresh()->date_of_birth?->format('Y-m-d')
        );
    }

    public function test_child_cannot_select_an_unapproved_avatar(): void
    {
        $child = Child::factory()->create([
            'avatar' => '🚀',
        ]);

        $this->actingAs($child->user);

        Livewire::test(Edit::class)
            ->set('avatar', '<script>alert(1)</script>')
            ->call('save')
            ->assertHasErrors(['avatar']);

        $this->assertSame('🚀', $child->fresh()->avatar);
    }

    public function test_guest_cannot_access_the_edit_profile_page(): void
    {
        $this->get(route('child.profile.edit'))
            ->assertRedirect(route('login'));
    }
}
