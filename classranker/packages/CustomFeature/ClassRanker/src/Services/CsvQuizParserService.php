<?php

namespace CustomFeature\ClassRanker\Services;

use League\Csv\Reader;

class CsvQuizParserService
{
    /**
     * Parse CSV and extract quiz questions
     * 
     * Expected CSV format:
     * question_text, option_a, option_b, option_c, option_d, correct_options, explanation
     */
    public function parse(string $filePath): array
    {
        try {
            $csv = Reader::createFromPath($filePath, 'r');
            $csv->setHeaderOffset(0); // First row is header

            $questions = [];

            foreach ($csv as $record) {
                $question = $this->parseRecord($record);
                if ($question) {
                    $questions[] = $question;
                }
            }

            return $questions;
        } catch (\Exception $e) {
            throw new \Exception('Failed to parse CSV: ' . $e->getMessage());
        }
    }

    protected function parseRecord(array $record): ?array
    {
        $questionText = $record['question_text'] ?? '';
        
        if (empty($questionText)) {
            return null;
        }

        $options = [];
        foreach (['a', 'b', 'c', 'd', 'e', 'f'] as $letter) {
            $optionKey = 'option_' . $letter;
            if (isset($record[$optionKey]) && !empty($record[$optionKey])) {
                $options[] = [
                    'letter' => $letter,
                    'text' => trim($record[$optionKey]),
                    'useTinymce' => false,
                ];
            }
        }

        // Parse correct options (comma-separated: "a,c" or "0,2")
        $correctOptions = [];
        if (isset($record['correct_options'])) {
            $correctParts = explode(',', $record['correct_options']);
            foreach ($correctParts as $part) {
                $part = trim($part);
                if (is_numeric($part)) {
                    $correctOptions[] = (int) $part;
                } else {
                    // Letter format (a, b, c)
                    $index = ord(strtolower($part)) - 97;
                    if ($index >= 0 && $index < count($options)) {
                        $correctOptions[] = $index;
                    }
                }
            }
        }

        return [
            'text' => $questionText,
            'options' => $options,
            'correctOptions' => $correctOptions,
            'explanation' => $record['explanation'] ?? '',
        ];
    }
}