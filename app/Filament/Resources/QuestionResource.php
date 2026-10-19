<?php

namespace App\Filament\Resources;

use App\Filament\Resources\QuestionResource\Pages;
use App\Filament\Resources\QuestionResource\RelationManagers;
use App\Models\Question;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;

class QuestionResource extends Resource
{
    protected static ?string $model = Question::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-question-mark-circle';

    protected static string|\UnitEnum|null $navigationGroup = 'Exams';

    protected static ?int $navigationSort = 2;

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Section::make('Question Details')
                    ->schema([
                        Forms\Components\Select::make('exam_id')
                            ->label('Exam')
                            ->relationship('exam', 'title')
                            ->searchable()
                            ->preload()
                            ->required(),
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
                    ])->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('exam.title')
                    ->label('Exam')
                    ->searchable()
                    ->sortable(),
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
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('exam_id')
                    ->relationship('exam', 'title')
                    ->searchable()
                    ->preload()
                    ->label('Exam'),
                Tables\Filters\SelectFilter::make('type')
                    ->options([
                        'multiple_choice' => 'Multiple Choice',
                        'true_false' => 'True/False',
                        'short_answer' => 'Short Answer',
                        'essay' => 'Essay',
                    ]),
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
        return [
            RelationManagers\OptionsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListQuestions::route('/'),
            'create' => Pages\CreateQuestion::route('/create'),
            'edit' => Pages\EditQuestion::route('/{record}/edit'),
        ];
    }
}
