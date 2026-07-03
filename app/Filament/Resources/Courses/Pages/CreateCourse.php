<?php

namespace App\Filament\Resources\Courses\Pages;

use App\Filament\Resources\Courses\CourseResource;
use App\Services\OpenAICurriculumGenerator;
use Filament\Actions;
use Filament\Actions\Action;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Pages\CreateRecord;

class CreateCourse extends CreateRecord
{
    protected static string $resource = CourseResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('generate_ai_curriculum')
                ->label('Generate AI Curriculum')
                ->color('success')
                ->schema([
                    TextInput::make('title')
                        ->required(),

                    Select::make('subject_id')
                        ->label('Subject')
                        ->relationship('subject', 'name')
                        ->required(),

                    Select::make('age_group')
                        ->options([
                            '3-5' => 'Tiny Learners (3-5)',
                            '6-9' => 'Explorers (6-9)',
                            '10-14' => 'Achievers (10-14)',
                        ])
                        ->required(),

                    Textarea::make('goal')
                        ->required(),
                ])
                ->action(function (array $data) {
                    $course = app(OpenAICurriculumGenerator::class)->generate([
                        'title' => $data['title'],
                        'subject_id' => $data['subject_id'],
                        'age_group' => $data['age_group'],
                        'goal' => $data['goal'],
                    ]);

                    $this->redirect(CourseResource::getUrl('edit', ['record' => $course]));
                }),
        ];
    }
}
