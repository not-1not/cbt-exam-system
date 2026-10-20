<?php

namespace App\Filament\Resources\ExamResource\RelationManagers;

use App\Services\QuestionImportService;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms;
use Filament\Notifications\Notification;
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
                CreateAction::make()
                    ->label('Tambah Soal Manual'),
                Tables\Actions\Action::make('bulkImport')
                    ->label('Import Soal Massal')
                    ->icon('heroicon-o-arrow-up-tray')
                    ->color('success')
                    ->modalHeading('Import Soal Massal ke Ujian Ini')
                    ->modalDescription('Salin teks soal dan pilihan jawaban dari Word/Docs ke kotak di bawah ini.')
                    ->modalSubmitActionLabel('Import Sekarang')
                    ->form([
                        Forms\Components\TextInput::make('default_points')
                            ->label('Poin per Soal')
                            ->numeric()
                            ->default(1)
                            ->required(),
                        Forms\Components\Textarea::make('content')
                            ->label('Teks Soal')
                            ->helperText('Tandai kunci jawaban dengan tanda bintang (*) di depan opsi (contoh: *C. Jawaban) atau tulis KUNCI: C di bawah opsi.')
                            ->placeholder("1. Apa ibu kota negara Indonesia?\nA. Bandung\nB. Surabaya\n*C. Jakarta\nD. Medan\n\n2. Berapakah hasil dari 5 x 5?\nA. 20\nB. 30\nC. 25\nD. 35\nKUNCI: C")
                            ->rows(14)
                            ->required(),
                    ])
                    ->action(function (array $data): void {
                        $count = QuestionImportService::import(
                            (int) $this->getOwnerRecord()->id,
                            $data['content'],
                            (int) $data['default_points']
                        );

                        if ($count > 0) {
                            Notification::make()
                                ->title("Berhasil mengimpor {$count} soal ke ujian ini!")
                                ->success()
                                ->send();
                        } else {
                            Notification::make()
                                ->title('Tidak ada soal yang berhasil diparsing. Periksa kembali format teks Anda.')
                                ->warning()
                                ->send();
                        }
                    }),
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
