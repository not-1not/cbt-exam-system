<?php

namespace App\Filament\Resources\QuestionResource\RelationManagers;

use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;

class OptionsRelationManager extends RelationManager
{
    protected static string $relationship = 'options';

    protected static ?string $recordTitleAttribute = 'text';

    protected static ?string $title = 'Options';

    public function form(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Forms\Components\Textarea::make('text')
                    ->label('Option Text')
                    ->required()
                    ->rows(2)
                    ->columnSpanFull(),
                Forms\Components\Toggle::make('is_correct')
                    ->label('Correct Answer')
                    ->default(false),
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
                    ->label('Option')
                    ->limit(50)
                    ->searchable(),
                Tables\Columns\IconColumn::make('is_correct')
                    ->label('Correct')
                    ->boolean()
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-x-circle')
                    ->trueColor('success')
                    ->falseColor('danger'),
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
