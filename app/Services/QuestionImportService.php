<?php

namespace App\Services;

use App\Models\Option;
use App\Models\Question;
use Illuminate\Support\Facades\DB;

class QuestionImportService
{
    /**
     * Parse raw text containing multiple questions and options.
     *
     * @return array<int, array{text: string, options: array<int, array{letter: string, text: string, is_correct: bool}>}>
     */
    public static function parse(string $content): array
    {
        $lines = preg_split('/\r\n|\r|\n/', $content);
        $questions = [];
        $currentQuestion = null;

        foreach ($lines as $rawLine) {
            $line = trim($rawLine);
            if ($line === '') {
                continue;
            }

            // 1. Detect Option line: e.g. "*A. Jakarta", "A. Bandung", "*A) Jakarta", "(A) Jakarta", "a. Jakarta"
            if (preg_match('/^(\*?)\s*(?:(?:\(([a-eA-E])\))|([a-eA-E])[\.\)])\s+(.+)$/u', $line, $matches)) {
                if ($currentQuestion !== null) {
                    $isStarred = $matches[1] === '*';
                    $letter = strtoupper($matches[2] !== '' ? $matches[2] : $matches[3]);
                    $optionText = trim($matches[4]);

                    $currentQuestion['options'][] = [
                        'letter' => $letter,
                        'text' => $optionText,
                        'is_correct' => $isStarred,
                    ];
                    continue;
                }
            }

            // 2. Detect Answer / Key line: e.g. "KUNCI: C", "KUNCI JAWABAN: C", "ANSWER: C"
            if (preg_match('/^(?:kunci(?:\s+jawaban)?|answer)\s*[:=]\s*([a-eA-E])/i', $line, $matches)) {
                if ($currentQuestion !== null && count($currentQuestion['options']) > 0) {
                    $keyLetter = strtoupper($matches[1]);
                    $matchedByKey = false;
                    foreach ($currentQuestion['options'] as &$opt) {
                        if (($opt['letter'] ?? '') === $keyLetter) {
                            $opt['is_correct'] = true;
                            $matchedByKey = true;
                        } else {
                            $opt['is_correct'] = false;
                        }
                    }
                    unset($opt);

                    if (! $matchedByKey) {
                        // Fallback by index A=0, B=1, C=2, D=3, E=4
                        $idx = ord($keyLetter) - ord('A');
                        if (isset($currentQuestion['options'][$idx])) {
                            $currentQuestion['options'][$idx]['is_correct'] = true;
                        }
                    }
                    continue;
                }
            }

            // 3. Detect new Question line starting with a number e.g. "1. What is..." or "1) What is..."
            $isNumbered = preg_match('/^(?:(?:soal|no\.?)\s*)?\d+[\.\):]\s*(.+)$/iu', $line, $matches);

            if ($isNumbered || ($currentQuestion !== null && count($currentQuestion['options']) > 0)) {
                if ($currentQuestion !== null && count($currentQuestion['options']) > 0) {
                    $questions[] = $currentQuestion;
                }
                $qText = $isNumbered ? trim($matches[1]) : $line;
                $currentQuestion = [
                    'text' => $qText,
                    'options' => [],
                ];
            } else {
                if ($currentQuestion === null) {
                    $currentQuestion = [
                        'text' => $line,
                        'options' => [],
                    ];
                } else {
                    $currentQuestion['text'] .= "\n".$line;
                }
            }
        }

        if ($currentQuestion !== null && count($currentQuestion['options']) > 0) {
            $questions[] = $currentQuestion;
        }

        return $questions;
    }

    /**
     * Import parsed questions into an exam.
     *
     * @return int Number of questions imported
     */
    public static function import(int $examId, string $content, int $defaultPoints = 1): int
    {
        $parsedQuestions = self::parse($content);
        if (empty($parsedQuestions)) {
            return 0;
        }

        return DB::transaction(function () use ($examId, $parsedQuestions, $defaultPoints) {
            $maxOrder = Question::where('exam_id', $examId)->max('order') ?? 0;
            $count = 0;

            foreach ($parsedQuestions as $q) {
                $maxOrder++;
                $question = Question::create([
                    'exam_id' => $examId,
                    'text' => $q['text'],
                    'type' => 'multiple_choice',
                    'points' => $defaultPoints > 0 ? $defaultPoints : 1,
                    'order' => $maxOrder,
                ]);

                foreach ($q['options'] as $idx => $opt) {
                    Option::create([
                        'question_id' => $question->id,
                        'text' => $opt['text'],
                        'is_correct' => (bool) $opt['is_correct'],
                        'order' => $idx + 1,
                    ]);
                }

                $count++;
            }

            return $count;
        });
    }
}
