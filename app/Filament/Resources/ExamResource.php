<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ExamResource\Pages;
use App\Filament\Resources\ExamResource\RelationManagers;
use App\Models\Exam;
use App\Models\User;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;

class ExamResource extends Resource
{
    protected static ?string $model = Exam::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-document-text';

    protected static string|\UnitEnum|null $navigationGroup = 'Exams';

    protected static ?int $navigationSort = 1;

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Section::make('Exam Details')
                    ->schema([
                        Forms\Components\TextInput::make('title')
                            ->required()
                            ->maxLength(255),
                        Forms\Components\TextInput::make('code')
                            ->required()
                            ->maxLength(50)
                            ->unique(ignoreRecord: true)
                            ->helperText('Unique code for the exam'),
                        Forms\Components\Textarea::make('description')
                            ->columnSpanFull()
                            ->rows(3),
                        Forms\Components\TextInput::make('duration_minutes')
                            ->label('Duration (minutes)')
                            ->required()
                            ->numeric()
                            ->minValue(1)
                            ->default(60),
                        Forms\Components\DateTimePicker::make('start_at')
                            ->label('Start Date/Time'),
                        Forms\Components\DateTimePicker::make('end_at')
                            ->label('End Date/Time'),
                    ])->columns(2),

                Section::make('Settings')
                    ->schema([
                        Forms\Components\Toggle::make('shuffle_questions')
                            ->label('Shuffle Questions')
                            ->default(false),
                        Forms\Components\Toggle::make('shuffle_options')
                            ->label('Shuffle Options')
                            ->default(false),
                        Forms\Components\TextInput::make('max_attempts')
                            ->label('Max Attempts')
                            ->required()
                            ->numeric()
                            ->minValue(1)
                            ->default(1),
                        Forms\Components\TextInput::make('pass_mark')
                            ->label('Pass Mark (%)')
                            ->required()
                            ->numeric()
                            ->minValue(0)
                            ->maxValue(100)
                            ->step(0.01)
                            ->default(50.00),
                        Forms\Components\Toggle::make('show_result_at_end')
                            ->label('Show Result at End')
                            ->default(true),
                        Forms\Components\Toggle::make('is_published')
                            ->label('Published')
                            ->default(false),
                        Forms\Components\Select::make('proctor_id')
                            ->label('Proctor')
                            ->relationship('proctor', 'name')
                            ->searchable()
                            ->preload()
                            ->getOptionLabelFromRecordUsing(fn (User $record) => $record->name.' ('.$record->email.')'),
                    ])->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('title')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('code')
                    ->searchable()
                    ->sortable()
                    ->badge(),
                Tables\Columns\TextColumn::make('duration_minutes')
                    ->label('Duration (min)')
                    ->sortable(),
                Tables\Columns\IconColumn::make('shuffle_questions')
                    ->label('Shuffle Q')
                    ->boolean(),
                Tables\Columns\IconColumn::make('shuffle_options')
                    ->label('Shuffle O')
                    ->boolean(),
                Tables\Columns\TextColumn::make('max_attempts')
                    ->label('Max Attempts')
                    ->sortable(),
                Tables\Columns\TextColumn::make('pass_mark')
                    ->label('Pass Mark')
                    ->suffix('%')
                    ->sortable(),
                Tables\Columns\IconColumn::make('is_published')
                    ->label('Published')
                    ->boolean(),
                Tables\Columns\TextColumn::make('proctor.name')
                    ->label('Proctor')
                    ->searchable()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('start_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('end_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('is_published')
                    ->options([
                        '1' => 'Published',
                        '0' => 'Draft',
                    ])
                    ->label('Status'),
                Tables\Filters\SelectFilter::make('proctor_id')
                    ->relationship('proctor', 'name')
                    ->searchable()
                    ->preload()
                    ->label('Proctor'),
            ])
            ->actions([
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->bulkActions([
                DeleteBulkAction::make(),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            RelationManagers\QuestionsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListExams::route('/'),
            'create' => Pages\CreateExam::route('/create'),
            'edit' => Pages\EditExam::route('/{record}/edit'),
        ];
    }
}
