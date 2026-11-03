<?php

namespace App\Livewire\ParentPortal\Children;

use App\Models\User;
use App\Services\Child\Context\CurrentChildService;
use App\Services\ParentPortal\ParentChildManagementService;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Livewire\Component;
use Illuminate\Support\Str;
use App\Exceptions\PlanLimitReachedException;
use App\Services\Billing\EntitlementService;

class Index extends Component
{
    public string $first_name = '';
    public string $last_name = '';
    public string $username = '';
    public string $date_of_birth = '';
    public string $avatar = '🚀';

    public ?int $editingChildId = null;
    public string $editFirstName = '';
    public string $editLastName = '';
    public string $editDateOfBirth = '';
    public string $editAvatar = '🚀';

    public ?int $confirmDeleteChildId = null;
    public ?string $generatedPin = null;
    public ?string $generatedUsername = null;

    public array $availableAvatars = [
        '🚀',
        '🧑‍🚀',
        '🦊',
        '🐼',
        '🦁',
        '🐸',
        '🐯',
        '🐻',
        '🦄',
        '🐬',
        '🐢',
        '🐙',
    ];

    public function mount(): void
    {
        abort_unless(Auth::user() instanceof User && Auth::user()->isParent(), 403);
    }

    protected function createRules(): array
    {
        return [
            'first_name' => ['required', 'string', 'min:1', 'max:50'],
            'last_name' => ['nullable', 'string', 'max:50'],
            'username' => ['nullable', 'string', 'min:3', 'max:30', 'alpha_dash', Rule::unique('children', 'username')],
            'date_of_birth' => ['nullable', 'date', 'before:today'],
            'avatar' => ['required', 'string', Rule::in($this->availableAvatars)],
        ];
    }

    protected function editRules(): array
    {
        return [
            'editFirstName' => ['required', 'string', 'min:1', 'max:50'],
            'editLastName' => ['nullable', 'string', 'max:50'],
            'editDateOfBirth' => ['nullable', 'date', 'before:today'],
            'editAvatar' => ['required', 'string', Rule::in($this->availableAvatars)],
        ];
    }

    public function createChild(ParentChildManagementService $management): void
    {
        $parent = $this->parent();
        $this->username = Str::lower(trim($this->username));
        $validated = $this->validate($this->createRules());

        try {
            $result = $management->create($parent, $validated);
        } catch (PlanLimitReachedException $e) {
            $this->addError('first_name', $e->getMessage());

            return;
        }
        $this->generatedPin = $result['pin'];
        $this->generatedUsername = $result['child']->username;

        $this->reset(['first_name', 'last_name', 'username', 'date_of_birth']);
        $this->avatar = '🚀';
        $this->dispatch('child-created');
    }

    public function beginEdit(int $childId): void
    {
        $child = $this->parent()->children()->whereKey($childId)->firstOrFail();

        $this->editingChildId = $child->id;
        $this->editFirstName = $child->first_name;
        $this->editLastName = $child->last_name ?? '';
        $this->editDateOfBirth = $child->date_of_birth?->format('Y-m-d') ?? '';
        $this->editAvatar = $child->avatar ?: '🚀';
    }

    public function saveEdit(ParentChildManagementService $management): void
    {
        abort_unless($this->editingChildId, 404);
        $validated = $this->validate($this->editRules());

        $management->update($this->parent(), $this->editingChildId, [
            'first_name' => $validated['editFirstName'],
            'last_name' => $validated['editLastName'] ?? '',
            'date_of_birth' => $validated['editDateOfBirth'] ?? '',
            'avatar' => $validated['editAvatar'],
        ]);

        $this->editingChildId = null;
        session()->flash('parent_child_status', 'Child profile updated.');
    }

    public function cancelEdit(): void
    {
        $this->editingChildId = null;
        $this->reset(['editFirstName', 'editLastName', 'editDateOfBirth']);
        $this->editAvatar = '🚀';
        $this->resetValidation();
    }

    public function resetChildPin(int $childId, ParentChildManagementService $management): void
    {
        $this->generatedPin = $management->resetPin($this->parent(), $childId);

        $child = $this->parent()->children()->whereKey($childId)->firstOrFail();

        $this->generatedUsername = $child->username;

        session()->flash(
            'parent_child_status',
            'A new PIN was generated. Share it privately with your child.'
        );

        $this->dispatch('scroll-to-child-credentials');
    }

    public function setChildActive(
        int $childId,
        bool $active,
        ParentChildManagementService $management
    ): void {
        $child = $management->setActive($this->parent(), $childId, $active);

        session()->flash(
            'parent_child_status',
            $active
                ? "{$child->first_name}’s account is active again."
                : "{$child->first_name}’s account is paused. They can’t sign in until you resume it."
        );
    }

    public function askToRemove(int $childId): void
    {
        $this->parent()->children()->whereKey($childId)->firstOrFail();
        $this->confirmDeleteChildId = $childId;
    }

    public function cancelRemove(): void
    {
        $this->confirmDeleteChildId = null;
    }

    public function removeChild(ParentChildManagementService $management): void
    {
        abort_unless($this->confirmDeleteChildId, 404);
        $management->remove($this->parent(), $this->confirmDeleteChildId);
        $this->confirmDeleteChildId = null;
        session()->flash('parent_child_status', 'The child account and its associated learning history were permanently removed.');
    }

    public function switchToChild(int $childId, CurrentChildService $currentChild)
    {
        $child = $this->parent()->children()->whereKey($childId)->where('is_active', true)->firstOrFail();
        $currentChild->switchTo($child);

        return $this->redirectRoute('child.dashboard', navigate: true);
    }

    public function dismissCredentials(): void
    {
        $this->generatedPin = null;
        $this->generatedUsername = null;
    }

    private function parent(): User
    {
        $parent = Auth::user();
        abort_unless($parent instanceof User && $parent->isParent(), 403);

        return $parent;
    }

    public function render(): View
    {
        $parent = $this->parent();
        $entitlements = app(EntitlementService::class);

        return view('livewire.parent-portal.children.index', [
            'children' => $parent->children()->orderBy('first_name')->get(),
            'limitMessage' => $entitlements->canAddChild($parent)
                ? null
                : $entitlements->childLimitMessage($parent),
        ]);
    }
}
