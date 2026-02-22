<?php

namespace CustomFeature\ClassRanker\Services;

use Smalot\PdfParser\Parser as PdfParser;

class PdfQuizParserService
{
    protected $pdfParser;

    public function __construct()
    {
        $this->pdfParser = new PdfParser();
    }

    public function parse(string $filePath): array
    {
        try {
            $pdf = $this->pdfParser->parseFile($filePath);
            $text = $pdf->getText();
            $text = $this->cleanText($text);

            // Try MCQ format first
            $questions = $this->extractMCQQuestions($text);
            
            // If no MCQs found, try Q&A format
            if (empty($questions)) {
                $questions = $this->extractQAQuestions($text);
            }

            return $questions;
        } catch (\Exception $e) {
            throw new \Exception('Failed to parse PDF: ' . $e->getMessage());
        }
    }

    /**
     * Extract MCQ questions (Quiz_Questions format)
     */
    protected function extractMCQQuestions(string $text): array
    {
        $questions = [];
        
        // Pattern: "1. Question text" followed by options (a), (b), (c), (d)
        $pattern = '/(\d+)\.\s+(.+?)(?=\n\d+\.\s+|\z)/s';
        preg_match_all($pattern, $text, $blocks, PREG_SET_ORDER);

        foreach ($blocks as $block) {
            $questionText = trim($block[2]);
            
            // Check if this block has options
            if (!preg_match('/\([a-d]\)/', $questionText)) {
                continue;
            }

            $question = $this->parseMCQBlock($questionText);
            if ($question) {
                $questions[] = $question;
            }
        }

        return $questions;
    }

    /**
     * Parse MCQ block with options
     */
    protected function parseMCQBlock(string $block): ?array
    {
        // Extract main question (before first option)
        preg_match('/^(.*?)(?=\([a-d]\))/s', $block, $qMatch);
        $questionText = $this->cleanText(trim($qMatch[1] ?? ''));

        if (empty($questionText)) {
            return null;
        }

        // Extract options
        $options = [];
        preg_match_all('/\(([a-d])\)\s*([^\(]+?)(?=\([a-d]\)|Correct|Explanation|\z)/si', $block, $optMatches, PREG_SET_ORDER);

        foreach ($optMatches as $match) {
            $optionText = $this->cleanText(trim($match[2]));
            if (!empty($optionText)) {
                $options[] = [
                    'text' => $optionText,
                    'useTinymce' => false,
                ];
            }
        }

        if (count($options) < 2) {
            return null;
        }

        // Extract correct answer
        $correctOptions = [];
        if (preg_match('/Correct\s+option:\s*\(([a-d])\)/i', $block, $correctMatch)) {
            $letter = strtolower($correctMatch[1]);
            $index = ord($letter) - 97; // a=0, b=1, c=2, d=3
            if ($index >= 0 && $index < count($options)) {
                $correctOptions[] = $index;
            }
        }

        // Extract explanation
        $explanation = '';
        if (preg_match('/Explanation:\s*(.+?)(?=\z)/si', $block, $explMatch)) {
            $explanation = $this->cleanText(trim($explMatch[1]));
        }

        return [
            'text' => $questionText,
            'options' => $options,
            'correctOptions' => $correctOptions,
            'explanation' => $explanation,
        ];
    }

    /**
     * Extract Q&A questions (Question_Answer format)
     * Convert to MCQ format
     */
    protected function extractQAQuestions(string $text): array
    {
        $questions = [];
        
        // Pattern: "1. (a) Question" or "1. Question"
        $pattern = '/(\d+)\.\s+(.+?)(?=\n\d+\.\s+|Answer\s*\z|\z)/si';
        preg_match_all($pattern, $text, $blocks, PREG_SET_ORDER);

        foreach ($blocks as $block) {
            $fullBlock = $block[0];
            
            // Skip if this is "Answer" section
            if (stripos($fullBlock, 'Answer') === 0) {
                continue;
            }

            $question = $this->parseQABlock($fullBlock);
            if ($question) {
                $questions[] = $question;
            }
        }

        return $questions;
    }

    /**
     * Parse Q&A block and convert to MCQ format
     */
    protected function parseQABlock(string $block): ?array
    {
        // Extract question parts (a), (b), (c), etc.
        $parts = [];
        preg_match_all('/\(([a-z])\)\s*([^\(]+?)(?=\([a-z]\)|\z)/si', $block, $partMatches, PREG_SET_ORDER);

        if (count($partMatches) < 2) {
            // Single question without parts
            $lines = explode("\n", $block);
            $questionText = $this->cleanText($lines[0] ?? '');
            
            if (empty($questionText)) {
                return null;
            }

            // Create dummy options for Q&A type
            return [
                'text' => $questionText,
                'options' => [
                    ['text' => 'See full answer in explanation', 'useTinymce' => false],
                    ['text' => 'Answer provided below', 'useTinymce' => false],
                ],
                'correctOptions' => [0],
                'explanation' => $this->cleanText(implode(' ', array_slice($lines, 1))),
            ];
        }

        // Multi-part question - create separate MCQ for first part
        $firstPart = $partMatches[0];
        $questionText = $this->cleanText(trim($firstPart[2]));

        if (empty($questionText)) {
            return null;
        }

        // Use other parts as options
        $options = [];
        foreach ($partMatches as $idx => $part) {
            if ($idx === 0) continue; // Skip first part (it's the question)
            
            $optText = $this->cleanText(trim($part[2]));
            if (!empty($optText)) {
                $options[] = [
                    'text' => $optText,
                    'useTinymce' => false,
                ];
            }
        }

        // If we don't have enough options, create dummy ones
        if (count($options) < 2) {
            $options = [
                ['text' => 'See answer in explanation', 'useTinymce' => false],
                ['text' => 'Answer provided below', 'useTinymce' => false],
            ];
        }

        return [
            'text' => $questionText,
            'options' => $options,
            'correctOptions' => [0],
            'explanation' => '',
        ];
    }

    /**
     * Clean text from malformed UTF-8 characters
     */
    protected function cleanText(string $text): string
    {
        // Remove null bytes
        $text = str_replace("\0", '', $text);
        
        // Convert to UTF-8
        if (!mb_check_encoding($text, 'UTF-8')) {
            $text = mb_convert_encoding($text, 'UTF-8', 'UTF-8');
        }
        
        // Remove invalid UTF-8
        $text = mb_convert_encoding($text, 'UTF-8', 'UTF-8');
        
        // Remove control characters except newlines
        $text = preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]/u', '', $text);
        
        // Normalize multiple spaces
        $text = preg_replace('/[ \t]+/u', ' ', $text);
        
        // Normalize multiple newlines
        $text = preg_replace('/\n{3,}/u', "\n\n", $text);
        
        return trim($text);
    }
}