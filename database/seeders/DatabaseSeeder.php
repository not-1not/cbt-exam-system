<?php

namespace Database\Seeders;

use App\Models\Exam;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call(RoleSeeder::class);

        $admin = User::firstOrCreate(
            ['email' => 'admin@exam.test'],
            ['name' => 'Admin User', 'password' => Hash::make('password'), 'email_verified_at' => now()]
        );
        $admin->assignRole('admin');

        $proctor = User::firstOrCreate(
            ['email' => 'proctor@exam.test'],
            ['name' => 'Proctor User', 'password' => Hash::make('password'), 'email_verified_at' => now()]
        );
        $proctor->assignRole('proctor');

        $student = User::firstOrCreate(
            ['email' => 'student@exam.test'],
            ['name' => 'Student User', 'password' => Hash::make('password'), 'email_verified_at' => now()]
        );
        $student->assignRole('student');

        $teacher = User::firstOrCreate(
            ['email' => 'teacher@exam.test'],
            ['name' => 'Teacher User', 'password' => Hash::make('password'), 'email_verified_at' => now()]
        );
        $teacher->assignRole('teacher');

        $exam = Exam::firstOrCreate(
            ['code' => 'SIMULASI-01'],
            [
                'title' => 'Simulasi Ujian CBT',
                'description' => 'Paket ujian simulasi untuk latihan dan uji coba sistem CBT.',
                'duration_minutes' => 60,
                'shuffle_questions' => true,
                'shuffle_options' => true,
                'max_attempts' => 3,
                'pass_mark' => 70,
                'show_result_at_end' => true,
                'is_published' => true,
            ]
        );

        if ($exam->questions()->count() === 0) {
            $questionsData = [
                [
                    'text' => 'Apa kepanjangan dari CBT dalam konteks ujian sekolah?',
                    'points' => 20,
                    'options' => [
                        ['text' => 'Computer-Based Test (Ujian Berbasis Komputer)', 'is_correct' => true],
                        ['text' => 'Central Board Test', 'is_correct' => false],
                        ['text' => 'Computer Basic Training', 'is_correct' => false],
                        ['text' => 'Cyber-Based Technology', 'is_correct' => false],
                    ],
                ],
                [
                    'text' => 'Pancasila sebagai dasar negara Indonesia pertama kali dirumuskan dalam sidang BPUPKI pada tanggal...',
                    'points' => 20,
                    'options' => [
                        ['text' => '1 Juni 1945', 'is_correct' => true],
                        ['text' => '17 Agustus 1945', 'is_correct' => false],
                        ['text' => '28 Oktober 1928', 'is_correct' => false],
                        ['text' => '2 Mei 1945', 'is_correct' => false],
                    ],
                ],
                [
                    'text' => 'Sebuah kendaraan bergerak dengan kecepatan tetap 60 km/jam. Berapa jarak yang ditempuh kendaraan tersebut dalam waktu 2,5 jam?',
                    'points' => 20,
                    'options' => [
                        ['text' => '150 km', 'is_correct' => true],
                        ['text' => '120 km', 'is_correct' => false],
                        ['text' => '140 km', 'is_correct' => false],
                        ['text' => '180 km', 'is_correct' => false],
                    ],
                ],
                [
                    'text' => 'Manakah di bawah ini yang merupakan perangkat keras input (masukan) pada komputer?',
                    'points' => 20,
                    'options' => [
                        ['text' => 'Keyboard dan Mouse', 'is_correct' => true],
                        ['text' => 'Monitor dan Speaker', 'is_correct' => false],
                        ['text' => 'Printer dan Proyektor', 'is_correct' => false],
                        ['text' => 'Harddisk dan Flashdisk', 'is_correct' => false],
                    ],
                ],
                [
                    'text' => 'Dalam ilmu biologi, organel sel yang berfungsi sebagai pusat penghasil energi utama (ATP) adalah...',
                    'points' => 20,
                    'options' => [
                        ['text' => 'Mitokondria', 'is_correct' => true],
                        ['text' => 'Ribosom', 'is_correct' => false],
                        ['text' => 'Badan Golgi', 'is_correct' => false],
                        ['text' => 'Retikulum Endoplasma', 'is_correct' => false],
                    ],
                ],
            ];

            foreach ($questionsData as $index => $qData) {
                $question = $exam->questions()->create([
                    'text' => $qData['text'],
                    'type' => 'multiple_choice',
                    'points' => $qData['points'],
                    'order' => $index + 1,
                ]);

                foreach ($qData['options'] as $optIndex => $optData) {
                    $question->options()->create([
                        'text' => $optData['text'],
                        'is_correct' => $optData['is_correct'],
                        'order' => $optIndex + 1,
                    ]);
                }
            }
        }
    }
}
