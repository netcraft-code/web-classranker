<?php

namespace CustomFeature\Quiz\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateQuizRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request
     */
    public function rules(): array
    {
        $quizId = $this->id;

        return [
            'title' => 'required|string|max:255',
            'slug' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'status' => 'nullable|boolean',
            'is_premium' => 'nullable|boolean',
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
        ];
    }

    /**
     * Get custom messages for validator errors
     */
    public function messages(): array
    {
        return [
            'chapters.required' => 'Please select at least one chapter.',
            'questions.required' => 'Please add at least one question.',
            'questions.*.options.min' => 'Each question must have at least 2 options.',
        ];
    }
}