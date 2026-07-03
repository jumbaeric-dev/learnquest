<?php

namespace App\Filament\Resources\CourseProgress\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class CourseProgressTable
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

                TextColumn::make('course.title')
                    ->label('Course')
                    ->searchable(),

                TextColumn::make('completed_lessons'),

                TextColumn::make('total_lessons'),

                TextColumn::make('progress_percentage')
                    ->suffix('%'),

                IconColumn::make('completed')
                    ->boolean(),

                TextColumn::make('completed_at')
                    ->dateTime(),
            ])
            ->filters([
                //
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
