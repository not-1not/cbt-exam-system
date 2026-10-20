<?php

namespace App\Filament\Resources\QuestionResource\Pages;

use App\Filament\Resources\QuestionResource;
use App\Models\Exam;
use App\Services\QuestionImportService;
use Filament\Actions\Action;
use Filament\Actions\CreateAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;

class ListQuestions extends ListRecords
{
    protected static string $resource = QuestionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->label('Tambah Soal Manual'),
            Action::make('bulkImport')
                ->label('Import Soal Massal')
                ->icon('heroicon-o-arrow-up-tray')
                ->color('success')
                ->modalHeading('Import Soal Massal (Copy-Paste)')
                ->modalDescription('Salin teks soal dan pilihan jawaban dari Word/Docs ke kotak di bawah ini.')
                ->modalSubmitActionLabel('Import Sekarang')
                ->form([
                    Select::make('exam_id')
                        ->label('Pilih Ujian Tujuan')
                        ->options(fn () => Exam::pluck('title', 'id'))
                        ->required()
                        ->searchable(),
                    TextInput::make('default_points')
                        ->label('Poin per Soal')
                        ->numeric()
                        ->default(1)
                        ->required(),
                    Textarea::make('content')
                        ->label('Teks Soal')
                        ->helperText('Tandai kunci jawaban dengan tanda bintang (*) di depan opsi (contoh: *C. Jawaban) atau tulis KUNCI: C di bawah opsi.')
                        ->placeholder("1. Apa ibu kota negara Indonesia?\nA. Bandung\nB. Surabaya\n*C. Jakarta\nD. Medan\n\n2. Berapakah hasil dari 5 x 5?\nA. 20\nB. 30\nC. 25\nD. 35\nKUNCI: C")
                        ->rows(14)
                        ->required(),
                ])
                ->action(function (array $data): void {
                    $count = QuestionImportService::import(
                        (int) $data['exam_id'],
                        $data['content'],
                        (int) $data['default_points']
                    );

                    if ($count > 0) {
                        Notification::make()
                            ->title("Berhasil mengimpor {$count} soal!")
                            ->success()
                            ->send();
                    } else {
                        Notification::make()
                            ->title('Tidak ada soal yang berhasil diparsing. Periksa kembali format teks Anda.')
                            ->warning()
                            ->send();
                    }
                }),
        ];
    }
}
