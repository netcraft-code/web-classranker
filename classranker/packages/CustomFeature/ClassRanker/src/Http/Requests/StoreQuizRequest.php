<?php

namespace CustomFeature\ClassRanker\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreQuizRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request
     */
    public function authorize(): bool
    {
        return true; // Adjust based on your authorization logic
    }

    /**
     * Get the validation rules that apply to the request
     */
    public function rules(): array
    {
        return [
            'title' => 'required|string|max:255',
            'slug' => 'required|string|max:255|unique:quizzes,slug',
            'description' => 'nullable|string|max:1000',
            'status' => 'nullable|boolean',

            // Chapters validation
            'chapters' => 'required|array|min:1',
            'chapters.*.board_id' => 'required|exists:boards,id',
            'chapters.*.grade_id' => 'required|exists:grades,id',
            'chapters.*.subject_id' => 'required|exists:subjects,id',
            'chapters.*.book_id' => 'required|exists:books,id',
            'chapters.*.chapter_id' => 'required|exists:chapters,id',

            // Questions validation
            'questions' => 'required|array|min:1',
            'questions.*.text' => 'required|string',
            'questions.*.order' => 'nullable|integer|min:0',
            'questions.*.options' => 'required|array|min:2|max:6',
            'questions.*.options.*.text' => 'required|string',
            'questions.*.options.*.use_tinymce' => 'nullable|boolean',
            'questions.*.correct_options' => 'required|array|min:1',
        ];
    }

    /**
     * Get custom attributes for validator errors
     */
    public function attributes(): array
    {
        return [
            'title' => 'quiz title',
            'slug' => 'quiz slug',
            'description' => 'quiz description',
            'chapters' => 'chapters',
            'questions' => 'questions',
            'questions.*.text' => 'question text',
            'questions.*.options' => 'question options',
            'questions.*.options.*.text' => 'option text',
            'questions.*.correct_option' => 'correct answer',
        ];
    }

    /**
     * Get custom messages for validator errors
     */
    public function messages(): array
    {
        return [
            'chapters.required' => 'Please select at least one chapter.',
            'chapters.min' => 'Please select at least one chapter.',
            'questions.required' => 'Please add at least one question.',
            'questions.min' => 'Please add at least one question.',
            'questions.*.options.min' => 'Each question must have at least 2 options.',
            'questions.*.options.max' => 'Each question can have maximum 6 options.',
        ];
    }
}