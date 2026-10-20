<?php

use App\Livewire\ExamDashboard;
use App\Livewire\ExamInstructions;
use App\Livewire\ExamResult;
use App\Livewire\ExamTake;
use App\Models\Exam;
use App\Models\ExamAttempt;
use App\Models\ExamAttemptAnswer;
use App\Models\Question;
use App\Models\User;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
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

        // Guarantee role assignments for standard demo accounts
        $admin = User::firstOrCreate(
            ['email' => 'admin@exam.test'],
            ['name' => 'Admin User', 'password' => Hash::make('password'), 'email_verified_at' => now()]
        );
        $admin->syncRoles(['admin']);

        $student = User::firstOrCreate(
            ['email' => 'student@exam.test'],
            ['name' => 'Student User', 'password' => Hash::make('password'), 'email_verified_at' => now()]
        );
        $student->syncRoles(['student']);

        $teacher = User::firstOrCreate(
            ['email' => 'teacher@exam.test'],
            ['name' => 'Teacher User', 'password' => Hash::make('password'), 'email_verified_at' => now()]
        );
        $teacher->syncRoles(['teacher']);

        $proctor = User::firstOrCreate(
            ['email' => 'proctor@exam.test'],
            ['name' => 'Proctor User', 'password' => Hash::make('password'), 'email_verified_at' => now()]
        );
        $proctor->syncRoles(['proctor']);

        // Reset demo student's previous empty attempts so student can start fresh with questions
        ExamAttemptAnswer::whereHas('attempt', function ($q) use ($student) {
            $q->where('user_id', $student->id);
        })->delete();
        ExamAttempt::where('user_id', $student->id)->delete();

        $userCount = User::count();
        $examCount = Exam::count();
        $questionsCount = Question::count();
        $usersList = User::with('roles')->get()->map(function ($u) {
            return [
                'email' => $u->email,
                'name' => $u->name,
                'roles' => $u->roles->pluck('name')->all(),
            ];
        });

        return response()->json([
            'status' => 'success',
            'message' => 'Database successfully connected, migrated, and seeded with questions!',
            'driver' => $driver,
            'database' => $database,
            'users_count' => $userCount,
            'exams_count' => $examCount,
            'questions_count' => $questionsCount,
            'users' => $usersList,
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

Route::middleware(['auth'])->group(function () {
    Route::middleware('role:student,admin,teacher,proctor')->group(function () {
        Route::get('/exam', ExamDashboard::class)->name('exam.dashboard');
        Route::get('/exam/{exam}/instructions', ExamInstructions::class)->name('exam.instructions');
        Route::get('/exam/attempt/{attempt}/take', ExamTake::class)->name('exam.take');
        Route::get('/exam/attempt/{attempt}/result', ExamResult::class)->name('exam.result');
    });
});

require __DIR__.'/auth.php';
