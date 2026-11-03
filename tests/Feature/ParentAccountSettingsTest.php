<?php

namespace Tests\Feature;

use App\Livewire\ParentPortal\Account\Settings;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Route;
use Livewire\Livewire;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class ParentAccountSettingsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        app(\Spatie\Permission\PermissionRegistrar::class)->forgetCachedPermissions();
        Role::findOrCreate('parent', 'web');
    }

    private function parentUser(array $attributes = []): User
    {
        $parent = User::factory()->create($attributes + [
            'name' => 'Parent One',
            'email' => 'parent@example.test',
            'password' => 'CurrentPassword123!',
        ]);
        $parent->assignRole('parent');

        return $parent;
    }

    public function test_parent_can_view_account_settings(): void
    {
        $parent = $this->parentUser();

        $this->actingAs($parent)
            ->get(route('parent.account'))
            ->assertOk()
            ->assertSee('Account settings')
            ->assertSee('Password &amp; security', false);
    }

    public function test_non_parent_cannot_view_parent_account_settings(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->get(route('parent.account'))->assertForbidden();
    }

    public function test_parent_can_update_profile_information(): void
    {
        $parent = $this->parentUser();

        Livewire::actingAs($parent)
            ->test(Settings::class)
            ->set('name', 'Updated Parent')
            ->set('email', 'updated@example.test')
            ->call('saveProfile')
            ->assertHasNoErrors()
            ->assertSee('Your profile information has been updated.');

        $this->assertDatabaseHas('users', [
            'id' => $parent->id,
            'name' => 'Updated Parent',
            'email' => 'updated@example.test',
        ]);
    }

    public function test_parent_cannot_use_another_accounts_email(): void
    {
        $parent = $this->parentUser();
        User::factory()->create(['email' => 'taken@example.test']);

        Livewire::actingAs($parent)
            ->test(Settings::class)
            ->set('email', 'taken@example.test')
            ->call('saveProfile')
            ->assertHasErrors(['email']);
    }

    public function test_parent_must_supply_current_password_to_change_password(): void
    {
        $parent = $this->parentUser();

        Livewire::actingAs($parent)
            ->test(Settings::class)
            ->set('currentPassword', 'incorrect-password')
            ->set('password', 'NewSecurePassword123!')
            ->set('passwordConfirmation', 'NewSecurePassword123!')
            ->call('updatePassword')
            ->assertHasErrors(['currentPassword']);

        $this->assertTrue(Hash::check('CurrentPassword123!', $parent->fresh()->password));
    }

    public function test_parent_routes_require_session_authentication(): void
    {
        $route = Route::getRoutes()->getByName('parent.account');

        $this->assertNotNull($route);
        $this->assertContains('auth.session', $route->gatherMiddleware());
    }

    public function test_password_change_updates_current_session_password_hash(): void
    {
        $parent = $this->parentUser();

        Livewire::actingAs($parent)
            ->test(Settings::class)
            ->set('currentPassword', 'CurrentPassword123!')
            ->set('password', 'NewSecurePassword123!')
            ->set('passwordConfirmation', 'NewSecurePassword123!')
            ->call('updatePassword')
            ->assertHasNoErrors()
            ->assertSee('Your password has been changed successfully.');

        $freshParent = $parent->fresh();

        $this->assertSame(
            Auth::guard('web')->hashPasswordForCookie($freshParent->getAuthPassword()),
            session()->get('password_hash_web')
        );
        $this->assertTrue(Hash::check('NewSecurePassword123!', $freshParent->password));
    }

    public function test_parent_can_change_password_with_correct_current_password(): void
    {
        $parent = $this->parentUser();

        Livewire::actingAs($parent)
            ->test(Settings::class)
            ->set('currentPassword', 'CurrentPassword123!')
            ->set('password', 'NewSecurePassword123!')
            ->set('passwordConfirmation', 'NewSecurePassword123!')
            ->call('updatePassword')
            ->assertHasNoErrors()
            ->assertSee('Your password has been changed successfully.');

        $this->assertTrue(Hash::check('NewSecurePassword123!', $parent->fresh()->password));
    }
}