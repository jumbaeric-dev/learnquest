<?php

namespace App\Services\ParentPortal;

use App\Models\Child;
use App\Models\User;
use App\Services\Child\ChildAccountService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use App\Services\Billing\EntitlementService;

class ParentChildManagementService
{
    public function __construct(
        private readonly ChildAccountService $accounts,
        private readonly EntitlementService $entitlements,
    ) {}

    public function create(User $parent, array $data): array
    {
        $this->ensureParent($parent);
        $this->entitlements->assertCanAddChild($parent);
        $pin = $this->generatePin();

        $child = $this->accounts->createChild([
            'parent_id' => $parent->id,
            'first_name' => trim($data['first_name']),
            'last_name' => trim((string) ($data['last_name'] ?? '')),
            'username' => trim((string) ($data['username'] ?? '')),
            'date_of_birth' => $data['date_of_birth'] ?: null,
            'avatar' => $data['avatar'],
            'pin' => $pin,
        ]);

        return ['child' => $child, 'pin' => $pin];
    }

    public function update(User $parent, int $childId, array $data): Child
    {
        $child = $this->ownedChild($parent, $childId);

        $child->forceFill([
            'first_name' => trim($data['first_name']),
            'last_name' => trim((string) ($data['last_name'] ?? '')),
            'date_of_birth' => $data['date_of_birth'] ?: null,
            'avatar' => $data['avatar'],
        ])->save();

        return $child->refresh();
    }

    public function resetPin(User $parent, int $childId): string
    {
        $child = $this->ownedChild($parent, $childId);
        $pin = $this->generatePin();

        $child->forceFill(['pin' => Hash::make($pin)])->save();

        return $pin;
    }

    public function setActive(User $parent, int $childId, bool $active): Child
    {
        $child = $this->ownedChild($parent, $childId);

        DB::transaction(function () use ($child, $childId, $active): void {
            $child->forceFill(['is_active' => $active])->save();

            if (! $active && (int) session('active_child_id') === $childId) {
                session()->forget('active_child_id');
            }
        });

        return $child->refresh();
    }

    public function remove(User $parent, int $childId): void
    {
        $child = $this->ownedChild($parent, $childId);

        DB::transaction(function () use ($child, $childId): void {
            if ((int) session('active_child_id') === $childId) {
                session()->forget('active_child_id');
            }

            $childUser = $child->user;
            $child->delete();

            if ($childUser && ! Child::query()->where('user_id', $childUser->id)->exists()) {
                $childUser->delete();
            }
        });
    }

    private function ownedChild(User $parent, int $childId): Child
    {
        $this->ensureParent($parent);

        return $parent->children()->whereKey($childId)->firstOrFail();
    }

    private function ensureParent(User $parent): void
    {
        abort_unless($parent->isParent(), 403);
    }

    private function generatePin(): string
    {
        return (string) random_int(1000, 9999);
    }
}
