<?php

namespace Tests\Feature;

use App\Models\Child;
use App\Models\User;
use App\Services\Child\Context\CurrentChildService;
use App\Services\ParentPortal\ParentChildManagementService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Livewire\Livewire;
use Spatie\Permission\Models\Role;
use Tests\TestCase;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class ParentChildManagementTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();
        Role::findOrCreate('parent', 'web');
        Role::findOrCreate('child', 'web');
    }

    private function parentAccount(): User
    {
        $parent = User::factory()->create();
        $parent->assignRole('parent');

        return $parent;
    }

    private function childFor(User $parent, array $attributes = []): Child
    {
        return Child::factory()->create(array_merge([
            'parent_id' => $parent->id,
        ], $attributes));
    }

    public function test_parent_can_open_child_management_page(): void
    {
        $parent = $this->parentAccount();

        $this->actingAs($parent)
            ->get(route('parent.children.index'))
            ->assertOk()
            ->assertSee('Manage children')
            ->assertSee('Add a child');
    }

    public function test_non_parent_cannot_open_child_management_page(): void
    {
        $this->actingAs(User::factory()->create())
            ->get(route('parent.children.index'))
            ->assertForbidden();
    }

    public function test_parent_can_create_child_with_parent_ownership_and_generated_pin(): void
    {
        $parent = $this->parentAccount();

        $component = Livewire::actingAs($parent)
            ->test(\App\Livewire\ParentPortal\Children\Index::class)
            ->set('first_name', 'Amina')
            ->set('last_name', 'Otieno')
            ->set('username', 'amina-otieno')
            ->set('avatar', '🦊')
            ->call('createChild')
            ->assertHasNoErrors()
            ->assertSet('generatedUsername', 'amina-otieno');

        $this->assertMatchesRegularExpression('/^\d{4}$/', $component->get('generatedPin'));

        $child = Child::where('username', 'amina-otieno')->firstOrFail();

        $this->assertSame($parent->id, $child->parent_id);
        $this->assertSame('Amina', $child->first_name);
        $this->assertSame('🦊', $child->avatar);
        $this->assertTrue($child->user->hasRole('child'));
        $this->assertTrue(Hash::check('1234', $child->pin) === false);
    }

    public function test_parent_can_update_only_profile_fields_of_owned_child(): void
    {
        $parent = $this->parentAccount();
        $child = $this->childFor($parent, ['first_name' => 'Amina']);

        Livewire::actingAs($parent)
            ->test(\App\Livewire\ParentPortal\Children\Index::class)
            ->call('beginEdit', $child->id)
            ->set('editFirstName', 'Amara')
            ->set('editLastName', 'Otieno')
            ->set('editAvatar', '🐼')
            ->call('saveEdit')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('children', [
            'id' => $child->id,
            'first_name' => 'Amara',
            'last_name' => 'Otieno',
            'avatar' => '🐼',
        ]);
    }

    public function test_parent_can_reset_child_pin_and_old_pin_stops_working(): void
    {
        $parent = $this->parentAccount();
        $child = $this->childFor($parent);
        $oldHash = $child->pin;

        $pin = app(ParentChildManagementService::class)->resetPin($parent, $child->id);

        $child->refresh();
        $this->assertNotSame($oldHash, $child->pin);
        $this->assertTrue(Hash::check($pin, $child->pin));
        $this->assertSame(4, strlen($pin));
        $this->assertFalse(Hash::check('1234', $child->pin));
    }

    public function test_parent_cannot_manage_another_familys_child(): void
    {
        $parent = $this->parentAccount();
        $otherParent = $this->parentAccount();
        $otherChild = $this->childFor($otherParent);

        $component = Livewire::actingAs($parent)
            ->test(\App\Livewire\ParentPortal\Children\Index::class);

        try {
            $component->call('beginEdit', $otherChild->id);

            $this->fail('Expected cross-family child access to be denied.');
        } catch (ModelNotFoundException) {
            $this->assertTrue(true);
        }

        try {
            app(ParentChildManagementService::class)->resetPin($parent, $otherChild->id);

            $this->fail('Expected cross-family PIN reset to be denied.');
        } catch (ModelNotFoundException) {
            $this->assertTrue(true);
        }
    }

    public function test_removing_child_deletes_child_account_and_cascaded_learning_records(): void
    {
        $parent = $this->parentAccount();
        $child = $this->childFor($parent);
        $childUserId = $child->user_id;

        app(ParentChildManagementService::class)->remove($parent, $child->id);

        $this->assertDatabaseMissing('children', ['id' => $child->id]);
        $this->assertDatabaseMissing('users', ['id' => $childUserId]);
    }

    public function test_parent_can_switch_to_owned_child_and_return_to_parent_portal(): void
    {
        $parent = $this->parentAccount();
        $child = $this->childFor($parent, ['is_active' => true]);
        $this->actingAs($parent);

        app(CurrentChildService::class)->switchTo($child);

        $this->assertSame($child->id, app(CurrentChildService::class)->current()?->id);
        $this->assertSame($child->id, session('active_child_id'));

        $this->post(route('parent.return'))->assertRedirect(route('parent.dashboard'));
        $this->assertNull(session('active_child_id'));
        $this->assertNull(app(CurrentChildService::class)->current());
    }

    public function test_parent_cannot_switch_to_another_familys_child(): void
    {
        $parent = $this->parentAccount();
        $otherParent = $this->parentAccount();
        $otherChild = $this->childFor($otherParent);
        $this->actingAs($parent);

        $this->expectException(\Symfony\Component\HttpKernel\Exception\HttpException::class);
        app(CurrentChildService::class)->switchTo($otherChild);
    }
}
