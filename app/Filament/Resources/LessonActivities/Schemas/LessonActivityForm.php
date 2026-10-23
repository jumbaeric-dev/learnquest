<?php

namespace App\Filament\Resources\LessonActivities\Schemas;

use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;

class LessonActivityForm
{
    public static function configure(Schema $schema): Schema
    {
        $isPublishedQuiz = fn (Get $get): bool =>
            $get('activity_type') === 'quiz'
            && (bool) $get('is_published');

        return $schema
            ->components([

                Section::make('Activity Information')
                    ->schema([

                        Select::make('lesson_id')
                            ->relationship('lesson', 'title')
                            ->searchable()
                            ->preload()
                            ->required(),

                        TextInput::make('title')
                            ->required()
                            ->maxLength(255),

                        Select::make('activity_type')
                            ->options([
                                'video' => 'Video',
                                'story' => 'Story',
                                'quiz' => 'Quiz',
                                'audio' => 'Audio',
                                'drag_drop' => 'Drag & Drop',
                                'drawing' => 'Drawing',
                                'flashcard' => 'Flashcards',
                                'ai_chat' => 'AI Chat',
                                'coding_challenge' => 'Coding Challenge',
                                'project' => 'Project',
                            ])
                            ->live()
                            ->required(),

                        Select::make('skills')
                            ->relationship('skills', 'name')
                            ->multiple()
                            ->searchable()
                            ->preload(),

                    ])
                    ->columns(2),

                Section::make('Activity Content')
                    ->schema([

                        // VIDEO
                        TextInput::make('content.video_url')
                            ->label('Video URL')
                            ->visible(fn(Get $get) => $get('activity_type') === 'video'),

                        TextInput::make('content.duration')
                            ->numeric()
                            ->label('Duration (seconds)')
                            ->visible(fn(Get $get) => $get('activity_type') === 'video'),

                        // STORY
                        Textarea::make('content.story_text')
                            ->label('Story')
                            ->rows(8)
                            ->visible(fn(Get $get) => $get('activity_type') === 'story'),

                        // QUIZ
                        Textarea::make('content.question')
                            ->label('Question')
                            ->rows(3)
                            ->maxLength(1000)
                            ->required($isPublishedQuiz)
                            ->visible(fn (Get $get): bool => $get('activity_type') === 'quiz'),

                        Repeater::make('content.options')
                            ->label('Answer Options')
                            ->schema([
                                TextInput::make('option')
                                    ->label('Option text')
                                    ->required()
                                    ->maxLength(255)
                                    ->live(onBlur: true),
                            ])
                            ->required($isPublishedQuiz)
                            ->visible(fn (Get $get): bool => $get('activity_type') === 'quiz')
                            ->minItems(fn (Get $get): int => $isPublishedQuiz($get) ? 2 : 0)
                            ->maxItems(6)
                            ->addActionLabel('Add answer option'),

                        Select::make('content.answer')
                            ->label('Correct Answer')
                            ->placeholder('Choose the correct answer')
                            ->options(function (Get $get): array {
                                $state = $get('content.options');
                                $options = is_array($state) ? array_values($state) : [];
                                $choices = [];

                                foreach ($options as $index => $option) {
                                    $label = is_array($option)
                                        ? ($option['option'] ?? '')
                                        : $option;
                                    $label = is_string($label) ? trim($label) : '';

                                    $choices[$index] = $label !== ''
                                        ? $label
                                        : 'Option '.($index + 1);
                                }

                                return $choices;
                            })
                            ->required($isPublishedQuiz)
                            ->rules(fn (Get $get): array => $isPublishedQuiz($get)
                                ? ['integer', 'min:0', 'max:'.max(0, count(is_array($get('content.options')) ? $get('content.options') : []) - 1)]
                                : [])
                            ->native(false)
                            ->visible(fn (Get $get): bool => $get('activity_type') === 'quiz'),

                        // AI CHAT
                        Textarea::make('content.system_prompt')
                            ->label('AI System Prompt')
                            ->rows(4)
                            ->visible(fn(Get $get) => $get('activity_type') === 'ai_chat'),

                        TextInput::make('content.goal')
                            ->label('Learning Goal')
                            ->visible(fn(Get $get) => $get('activity_type') === 'ai_chat'),

                        // CODING CHALLENGE
                        Textarea::make('content.challenge')
                            ->label('Coding Challenge')
                            ->rows(5)
                            ->visible(fn(Get $get) => $get('activity_type') === 'coding_challenge'),

                        Select::make('content.difficulty')
                            ->options([
                                'easy' => 'Easy',
                                'medium' => 'Medium',
                                'hard' => 'Hard',
                            ])
                            ->visible(fn(Get $get) => $get('activity_type') === 'coding_challenge'),

                        // PROJECT
                        Textarea::make('content.project_description')
                            ->label('Project Description')
                            ->rows(6)
                            ->visible(fn(Get $get) => $get('activity_type') === 'project'),

                    ])
                    ->columnSpanFull(),

                Section::make('Rewards & Publishing')
                    ->schema([

                        TextInput::make('position')
                            ->numeric()
                            ->default(1)
                            ->required(),

                        TextInput::make('xp_reward')
                            ->numeric()
                            ->default(5)
                            ->required(),

                        Toggle::make('is_published')
                            ->live()
                            ->default(false),

                    ])
                    ->columns(3),

            ]);
    }
}
