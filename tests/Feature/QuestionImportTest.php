<?php

namespace Tests\Feature;

use App\Models\Exam;
use App\Models\Question;
use App\Services\QuestionImportService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class QuestionImportTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_parse_and_import_questions_with_asterisk(): void
    {
        $exam = Exam::factory()->create();

        $content = "
1. Apa ibu kota Indonesia?
A. Bandung
B. Surabaya
*C. Jakarta
D. Medan

2. Berapakah 10 + 15?
*A. 25
B. 30
C. 35
D. 40
        ";

        $count = QuestionImportService::import($exam->id, $content, 2);

        $this->assertEquals(2, $count);
        $this->assertDatabaseCount('questions', 2);
        $this->assertDatabaseCount('options', 8);

        $q1 = Question::where('exam_id', $exam->id)->where('order', 1)->first();
        $this->assertEquals('Apa ibu kota Indonesia?', $q1->text);
        $this->assertEquals(2, $q1->points);

        $correctOption = $q1->options()->where('is_correct', true)->first();
        $this->assertNotNull($correctOption);
        $this->assertEquals('Jakarta', $correctOption->text);
    }

    public function test_can_parse_and_import_questions_with_kunci_keyword(): void
    {
        $exam = Exam::factory()->create();

        $content = "
1. Planet terdekat dari matahari adalah...
A. Venus
B. Merkurius
C. Bumi
D. Mars
KUNCI: B

2. Siapa presiden pertama RI?
A. Soeharto
B. B.J. Habibie
C. Ir. Soekarno
D. Abdurrahman Wahid
ANSWER: C
        ";

        $count = QuestionImportService::import($exam->id, $content, 1);

        $this->assertEquals(2, $count);
        $q1 = Question::where('exam_id', $exam->id)->where('order', 1)->first();
        $correct1 = $q1->options()->where('is_correct', true)->first();
        $this->assertEquals('Merkurius', $correct1->text);

        $q2 = Question::where('exam_id', $exam->id)->where('order', 2)->first();
        $correct2 = $q2->options()->where('is_correct', true)->first();
        $this->assertEquals('Ir. Soekarno', $correct2->text);
    }
}
