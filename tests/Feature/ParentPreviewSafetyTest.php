<?php

namespace Tests\Feature;

use App\Livewire\Child\Learning\Activity;
use App\Livewire\Child\Missions\Components\DailyChallenges;
use App\Livewire\Child\Nova\ChatWidget;
use App\Livewire\ParentPortal\Children\Index;
use App\Models\ActivityProgress;
use App\Models\Child;
use App\Models\LessonActivity;
use App\Models\NovaMessage;
use App\Models\User;
use App\Services\Child\Context\CurrentChildService;
use App\Services\Child\Learning\DailyChallengeService;
use App\Services\Child\Learning\LearningContentAccessService;
use App\Services\Nova\NovaConversationService;
use App\Services\ParentPortal\ParentChildManagementService;
use App\Services\Progress\LearningEngineService;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Livewire\Livewire;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class ParentPreviewSafetyTest extends TestCase
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
        $parent = User::factory()->create(['password' => 'secret-pass-123']);
        $parent->assignRole('parent');

        return $parent;
    }

    private function childFor(User $parent, array $attributes = []): Child
    {
        return Child::factory()->create(array_merge([
            'parent_id' => $parent->id,
            'is_active' => true,
        ], $attributes));
    }

    private function startPreview(User $parent, Child $child): void
    {
        $this->actingAs($parent);

        app(CurrentChildService::class)->switchTo($child);
    }

    public function test_preview_flag_is_true_for_parents_and_false_for_children(): void
    {
        $parent = $this->parentAccount();
        $child = $this->childFor($parent);

        $this->actingAs($parent);
        $this->assertTrue(app(CurrentChildService::class)->isParentPreview());

        $this->actingAs($child->user);
        $this->assertFalse(app(CurrentChildService::class)->isParentPreview());
    }

    public function test_nova_chat_in_preview_neither_replies_nor_saves_nor_reads_history(): void
    {
        $this->mock(NovaConversationService::class, function ($mock) {
            $mock->shouldNotReceive('history');
            $mock->shouldNotReceive('respond');
        });

        $parent = $this->parentAccount();
        $this->startPreview($parent, $this->childFor($parent));

        Livewire::test(ChatWidget::class)
            ->set('draft', 'Hello Nova')
            ->call('send')
            ->assertSet('draft', '')
            ->assertCount('messages', 1);

        $this->assertSame(0, NovaMessage::count());
    }

    public function test_preview_cannot_complete_an_activity(): void
    {
        $this->mock(LearningContentAccessService::class, function ($mock) {
            $mock->shouldReceive('activity')->andReturnArg(0);
        });

        $this->mock(LearningEngineService::class, function ($mock) {
            $mock->shouldNotReceive('completeActivity');
        });

        $parent = $this->parentAccount();
        $this->startPreview($parent, $this->childFor($parent));

        Livewire::test(Activity::class, [
            'activity' => LessonActivity::factory()->create(),
        ])->call('complete');

        $this->assertSame(0, ActivityProgress::count());
    }

    public function test_preview_cannot_claim_daily_challenge_xp(): void
    {
        $this->mock(DailyChallengeService::class, function ($mock) {
            $mock->shouldReceive('forChild')->andReturn([]);
            $mock->shouldNotReceive('claim');
        });

        $parent = $this->parentAccount();
        $this->startPreview($parent, $this->childFor($parent));

        Livewire::test(DailyChallenges::class)
            ->call('claim', 'ask_nova')
            ->assertRedirect(route('child.missions'));
    }

    public function test_parent_without_a_preview_is_redirected_from_child_pages(): void
    {
        $parent = $this->parentAccount();

        $this->actingAs($parent)
            ->get(route('child.dashboard'))
            ->assertRedirect(route('parent.children.index'));
    }

    public function test_pausing_a_child_blocks_login_and_ends_a_preview(): void
    {
        $parent = $this->parentAccount();
        $child = $this->childFor($parent, [
            'username' => 'amina',
            'pin' => Hash::make('1234'),
        ]);

        $this->startPreview($parent, $child);

        app(ParentChildManagementService::class)->setActive($parent, $child->id, false);

        $this->assertFalse($child->fresh()->is_active);
        $this->assertNull(session('active_child_id'));

        $this->post(route('child.login'), ['username' => 'amina', 'pin' => '1234'])
            ->assertSessionHasErrors();

        app(ParentChildManagementService::class)->setActive($parent, $child->id, true);

        $this->post(route('child.login'), ['username' => 'amina', 'pin' => '1234'])
            ->assertRedirect(route('child.dashboard'));
    }

    public function test_pause_action_works_from_the_children_page(): void
    {
        $parent = $this->parentAccount();
        $child = $this->childFor($parent);

        Livewire::actingAs($parent)
            ->test(Index::class)
            ->call('setChildActive', $child->id, false);

        $this->assertFalse($child->fresh()->is_active);
    }

    public function test_parent_cannot_pause_another_familys_child(): void
    {
        $parent = $this->parentAccount();
        $otherChild = $this->childFor($this->parentAccount());

        $this->expectException(ModelNotFoundException::class);

        app(ParentChildManagementService::class)->setActive($parent, $otherChild->id, false);
    }

    public function test_child_login_ignores_username_case_and_padding(): void
    {
        $child = $this->childFor($this->parentAccount(), [
            'username' => 'amina',
            'pin' => Hash::make('1234'),
        ]);

        $this->post(route('child.login'), ['username' => '  Amina ', 'pin' => '1234'])
            ->assertRedirect(route('child.dashboard'));

        $this->assertAuthenticatedAs($child->user);
    }

    public function test_new_usernames_are_stored_lowercase(): void
    {
        $parent = $this->parentAccount();

        Livewire::actingAs($parent)
            ->test(Index::class)
            ->set('first_name', 'Amina')
            ->set('username', 'Amina_K')
            ->call('createChild')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('children', ['username' => 'amina_k']);
    }

    public function test_parent_login_clears_a_stale_preview(): void
    {
        $parent = $this->parentAccount();
        $child = $this->childFor($parent);

        $this->withSession(['active_child_id' => $child->id])
            ->post(route('parent.login.submit'), [
                'email' => $parent->email,
                'password' => 'secret-pass-123',
            ]);

        $this->assertNull(session('active_child_id'));
    }
}