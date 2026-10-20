<?php

use App\Livewire\ExamDashboard;
use App\Livewire\ExamInstructions;
use App\Livewire\ExamResult;
use App\Livewire\ExamTake;
use App\Models\Exam;
use App\Models\User;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;

Route::get('/db-setup', function () {
    try {
        $db = DB::connection();
        $db->getPdo();
        $driver = $db->getDriverName();
        $database = $db->getDatabaseName();

        $output = [];
        Artisan::call('migrate', ['--force' => true]);
        $output[] = trim(Artisan::output());

        Artisan::call('db:seed', ['--force' => true]);
        $output[] = trim(Artisan::output());

        $userCount = User::count();
        $examCount = Exam::count();

        return response()->json([
            'status' => 'success',
            'message' => 'Database successfully connected, migrated, and seeded!',
            'driver' => $driver,
            'database' => $database,
            'users_count' => $userCount,
            'exams_count' => $examCount,
            'default_logins' => [
                'admin' => ['email' => 'admin@exam.test', 'password' => 'password'],
                'student' => ['email' => 'student@exam.test', 'password' => 'password'],
                'proctor' => ['email' => 'proctor@exam.test', 'password' => 'password'],
                'teacher' => ['email' => 'teacher@exam.test', 'password' => 'password'],
            ],
            'artisan_log' => $output,
        ], 200, [], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
    } catch (Throwable $e) {
        return response()->json([
            'status' => 'error',
            'message' => $e->getMessage(),
            'code' => $e->getCode(),
            'file' => $e->getFile().':'.$e->getLine(),
            'troubleshooting' => [
                '1_password' => 'Jika password Supabase ada karakter spesial (@, #, dll), pastikan di-encode (%40, %23) atau ubah password di Supabase ke huruf/angka sederhana.',
                '2_port' => 'Gunakan port 5432 (Session Pooler atau Direct Connection) pada connection string Supabase.',
                '3_ssl' => 'Supabase mewajibkan SSL (sslmode=require).',
            ],
        ], 500, [], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
    }
});

Route::get('/', function () {
    return view('welcome');
});

Route::middleware(['auth', 'verified'])->group(function () {
    Route::middleware('role:student')->group(function () {
        Route::get('/exam', ExamDashboard::class)->name('exam.dashboard');
        Route::get('/exam/{exam}/instructions', ExamInstructions::class)->name('exam.instructions');
        Route::get('/exam/attempt/{attempt}/take', ExamTake::class)->name('exam.take');
        Route::get('/exam/attempt/{attempt}/result', ExamResult::class)->name('exam.result');
    });
});

require __DIR__.'/auth.php';
