<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ExamAttemptResource\Pages;
use App\Models\ExamAttempt;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Forms;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;

class ExamAttemptResource extends Resource
{
    protected static ?string $model = ExamAttempt::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-clipboard-document-check';

    protected static string|\UnitEnum|null $navigationGroup = 'Exams';

    protected static ?int $navigationSort = 4;

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Forms\Components\Select::make('user_id')
                    ->label('User')
                    ->relationship('user', 'name')
                    ->searchable()
                    ->preload()
                    ->required(),
                Forms\Components\Select::make('exam_id')
                    ->label('Exam')
                    ->relationship('exam', 'title')
                    ->searchable()
                    ->preload()
                    ->required(),
                Forms\Components\Select::make('status')
                    ->label('Status')
                    ->options([
                        'in_progress' => 'In Progress',
                        'completed' => 'Completed',
                        'submitted' => 'Submitted',
                        'graded' => 'Graded',
                        'force_submitted' => 'Force Submitted',
                    ])
                    ->required()
                    ->default('in_progress'),
                Forms\Components\DateTimePicker::make('started_at')
                    ->label('Started At'),
                Forms\Components\DateTimePicker::make('ended_at')
                    ->label('Ended At'),
                Forms\Components\DateTimePicker::make('submitted_at')
                    ->label('Submitted At'),
                Forms\Components\TextInput::make('score')
                    ->label('Score')
                    ->numeric()
                    ->step(0.01)
                    ->minValue(0)
                    ->maxValue(100),
                Forms\Components\DateTimePicker::make('force_submitted_at')
                    ->label('Force Submitted At'),
                Forms\Components\Textarea::make('force_reason')
                    ->label('Force Reason')
                    ->rows(3)
                    ->columnSpanFull(),
            ])->columns(2);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('user.name')
                    ->label('User')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('exam.title')
                    ->label('Exam')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'in_progress' => 'warning',
                        'completed', 'graded', 'submitted' => 'success',
                        'force_submitted' => 'danger',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => ucfirst(str_replace('_', ' ', $state))),
                Tables\Columns\TextColumn::make('started_at')
                    ->label('Started At')
                    ->dateTime()
                    ->sortable(),
                Tables\Columns\TextColumn::make('ended_at')
                    ->label('Ended At')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('submitted_at')
                    ->label('Submitted At')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('score')
                    ->label('Score')
                    ->suffix('%')
                    ->sortable()
                    ->color(fn ($state) => $state !== null && $state >= 50 ? 'success' : 'danger'),
                Tables\Columns\TextColumn::make('force_submitted_at')
                    ->label('Force Submitted At')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'in_progress' => 'In Progress',
                        'completed' => 'Completed',
                        'submitted' => 'Submitted',
                        'graded' => 'Graded',
                        'force_submitted' => 'Force Submitted',
                    ]),
                Tables\Filters\SelectFilter::make('exam_id')
                    ->relationship('exam', 'title')
                    ->searchable()
                    ->preload()
                    ->label('Exam'),
                Tables\Filters\SelectFilter::make('user_id')
                    ->relationship('user', 'name')
                    ->searchable()
                    ->preload()
                    ->label('User'),
            ])
            ->actions([
                ViewAction::make(),
                EditAction::make(),
                Action::make('force_submit')
                    ->label('Force Submit')
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->requiresConfirmation()
                    ->modalHeading('Force Submit Exam Attempt')
                    ->modalDescription('This will mark the exam attempt as force submitted. The user will no longer be able to continue.')
                    ->schema([
                        Forms\Components\Textarea::make('force_reason')
                            ->label('Reason for Force Submit')
                            ->required()
                            ->rows(3)
                            ->columnSpanFull(),
                    ])
                    ->action(function (ExamAttempt $record, array $data): void {
                        if (! auth()->user()->can('force-submit', $record)) {
                            Notification::make()
                                ->title('Unauthorized')
                                ->body('You do not have permission to force submit this exam attempt.')
                                ->danger()
                                ->send();

                            return;
                        }

                        $record->update([
                            'status' => 'force_submitted',
                            'force_submitted_at' => now(),
                            'force_reason' => $data['force_reason'],
                            'ended_at' => now(),
                        ]);

                        Notification::make()
                            ->title('Exam Attempt Force Submitted')
                            ->body('The exam attempt has been force submitted.')
                            ->success()
                            ->send();
                    })
                    ->visible(fn (ExamAttempt $record): bool => auth()->user()?->can('force-submit', $record)),
                DeleteAction::make(),
            ])
            ->bulkActions([
                DeleteBulkAction::make(),
            ])
            ->defaultSort('created_at', 'desc');
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListExamAttempts::route('/'),
            'create' => Pages\CreateExamAttempt::route('/create'),
            'edit' => Pages\EditExamAttempt::route('/{record}/edit'),
            'view' => Pages\ViewExamAttempt::route('/{record}'),
        ];
    }
}
