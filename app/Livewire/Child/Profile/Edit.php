<?php

namespace App\Livewire\Child\Profile;

use App\Models\Child;
use App\Services\Child\Context\CurrentChildService;
use Illuminate\Contracts\View\View;
use Illuminate\Validation\Rule;
use Livewire\Component;

class Edit extends Component
{
    public string $first_name = '';

    public string $last_name = '';

    public string $date_of_birth = '';

    public string $avatar = '🚀';

    /**
     * Available avatars are deliberately restricted to known choices.
     * Children cannot submit arbitrary HTML or image paths.
     */
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

    public function mount(CurrentChildService $currentChild): void
    {
        $child = $currentChild->current();

        abort_unless($child, 403);

        $this->first_name = $child->first_name;
        $this->last_name = $child->last_name ?? '';
        $this->date_of_birth = $child->date_of_birth?->format('Y-m-d') ?? '';
        $this->avatar = $child->avatar ?: '🚀';
    }

    protected function rules(): array
    {
        return [
            'first_name' => ['required', 'string', 'min:1', 'max:50'],
            'last_name' => ['nullable', 'string', 'max:50'],
            'date_of_birth' => [
                'nullable',
                'date',
                'before:today',
            ],
            'avatar' => [
                'required',
                'string',
                Rule::in($this->availableAvatars),
            ],
        ];
    }

    protected function messages(): array
    {
        return [
            'first_name.required' => 'Please enter your first name.',
            'first_name.max' => 'Your first name must be 50 characters or fewer.',
            'last_name.max' => 'Your last name must be 50 characters or fewer.',
            'date_of_birth.before' => 'Your birthday must be in the past.',
            'avatar.in' => 'Please choose one of the available explorer avatars.',
        ];
    }

    public function save(CurrentChildService $currentChild)
    {
        $validated = $this->validate();

        $child = $currentChild->current();

        abort_unless($child, 403);

        // Explicitly update editable fields only.
        $child->first_name = trim($validated['first_name']);
        $child->last_name = trim($validated['last_name'] ?? '');
        $child->date_of_birth = $validated['date_of_birth'] ?: null;
        $child->avatar = $validated['avatar'];
        $child->save();

        session()->flash('profile_saved', 'Your explorer profile has been updated!');

        return $this->redirectRoute('child.profile', navigate: true);
    }

    public function render(): View
    {
        return view('livewire.child.profile.edit');
    }
}
