<?php

namespace App\Filament\Resources\ExamResource\RelationManagers;

use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;

class QuestionsRelationManager extends RelationManager
{
    protected static string $relationship = 'questions';

    protected static ?string $recordTitleAttribute = 'text';

    protected static ?string $title = 'Questions';

    public function form(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Forms\Components\Textarea::make('text')
                    ->label('Question Text')
                    ->required()
                    ->rows(3)
                    ->columnSpanFull(),
                Forms\Components\Select::make('type')
                    ->label('Question Type')
                    ->options([
                        'multiple_choice' => 'Multiple Choice',
                        'true_false' => 'True/False',
                        'short_answer' => 'Short Answer',
                        'essay' => 'Essay',
                    ])
                    ->default('multiple_choice')
                    ->required(),
                Forms\Components\TextInput::make('points')
                    ->label('Points')
                    ->required()
                    ->numeric()
                    ->minValue(1)
                    ->default(1),
                Forms\Components\TextInput::make('order')
                    ->label('Order')
                    ->required()
                    ->numeric()
                    ->minValue(0)
                    ->default(0),
            ])->columns(2);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('text')
            ->columns([
                Tables\Columns\TextColumn::make('order')
                    ->label('Order')
                    ->sortable(),
                Tables\Columns\TextColumn::make('text')
                    ->label('Question')
                    ->limit(50)
                    ->searchable(),
                Tables\Columns\TextColumn::make('type')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'multiple_choice' => 'primary',
                        'true_false' => 'success',
                        'short_answer' => 'warning',
                        'essay' => 'danger',
                        default => 'gray',
                    }),
                Tables\Columns\TextColumn::make('points')
                    ->label('Points')
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('type')
                    ->options([
                        'multiple_choice' => 'Multiple Choice',
                        'true_false' => 'True/False',
                        'short_answer' => 'Short Answer',
                        'essay' => 'Essay',
                    ]),
            ])
            ->headerActions([
                CreateAction::make(),
            ])
            ->actions([
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->bulkActions([
                DeleteBulkAction::make(),
            ])
            ->defaultSort('order');
    }
}
