<?php

namespace App\Filament\Resources\Children\Schemas;

use App\Models\Child;
use App\Services\Child\ChildAccountService;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;

class ChildForm
{
  public static function configure(Schema $schema): Schema
  {
    return $schema->components([
      Section::make("Child Information")
        ->schema([
          Select::make("parent_id")
            ->relationship("parent", "name")
            ->searchable()
            ->preload()
            ->required(),

          TextInput::make("first_name")
            ->required()
            ->maxLength(255)
            ->live(onBlur: true)
            ->afterStateUpdated(function (
              Get $get,
              Set $set,
              ?string $state
            ): void {
              $username = $get("username");

              /*
               * Automatically generate a username when:
               *
               * - no username exists yet, or
               * - the username is still the automatically
               *   generated username from the previous
               *   first name.
               *
               * A manually customized username is never
               * silently replaced.
               */
              $previousAutomaticUsername = str($get("first_name"))
                ->slug()
                ->toString();

              if (
                blank($username) ||
                $username === $previousAutomaticUsername
              ) {
                $set(
                  "username",
                  app(ChildAccountService::class)->suggestUsername($state ?? "")
                );
              }
            }),

          TextInput::make("last_name")->maxLength(255),

          TextInput::make("username")
            ->label("Child Username")
            ->required()
            ->maxLength(255)
            ->unique(
              table: Child::class,
              column: "username",
              ignoreRecord: true
            )
            ->helperText(function (Get $get): string {
              $username = trim((string) $get("username"));

              if ($username === "") {
                return "This is the username the child will use to log in.";
              }

              if (!Child::where("username", $username)->exists()) {
                return "Username is available. The child will use this username to log in.";
              }

              $suggestions = app(ChildAccountService::class)->suggestUsernames(
                $username,
                3
              );

              return "Username '{$username}' is already taken. " .
                "Try: " .
                implode(", ", $suggestions) .
                ".";
            })
            ->live(onBlur: true),

          DatePicker::make("date_of_birth"),

          FileUpload::make("avatar")
            ->image()
            ->directory("children"),
        ])
        ->columns(2),

      Section::make("Progress")
        ->schema([
          TextInput::make("xp")
            ->numeric()
            ->default(0)
            ->required(),

          TextInput::make("level")
            ->numeric()
            ->default(1)
            ->required(),

          Toggle::make("is_active")->default(true),
        ])
        ->columns(3),
    ]);
  }
}
