<?php

namespace App\Filament\Resources;

use App\Filament\Resources\OptionResource\Pages;
use App\Models\Option;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;

class OptionResource extends Resource
{
    protected static ?string $model = Option::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-check-circle';

    protected static string|\UnitEnum|null $navigationGroup = 'Exams';

    protected static ?int $navigationSort = 3;

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Forms\Components\Select::make('question_id')
                    ->label('Question')
                    ->relationship('question', 'text')
                    ->searchable()
                    ->preload()
                    ->required(),
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

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('question.text')
                    ->label('Question')
                    ->limit(40)
                    ->searchable()
                    ->sortable(),
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
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('question_id')
                    ->relationship('question', 'text')
                    ->searchable()
                    ->preload()
                    ->label('Question'),
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

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListOptions::route('/'),
            'create' => Pages\CreateOption::route('/create'),
            'edit' => Pages\EditOption::route('/{record}/edit'),
        ];
    }
}
