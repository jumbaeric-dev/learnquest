<?php

namespace App\Filament\Resources\LessonProgress\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class LessonProgressTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([

                TextColumn::make('child.full_name')
                    ->label('Child')
                    ->searchable([
                        'children.first_name',
                        'children.last_name',
                    ]),

                TextColumn::make('lesson.title')
                    ->label('Lesson')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('completed_activities')
                    ->sortable(),

                TextColumn::make('total_activities')
                    ->sortable(),

                TextColumn::make('progress_percentage')
                    ->suffix('%')
                    ->sortable(),

                IconColumn::make('completed')
                    ->boolean(),

                TextColumn::make('completed_at')
                    ->dateTime()
                    ->sortable(),

            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}