<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreNoteRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'title' => 'required|string|min:3|max:255',
            'content' => 'required|string|min:10|max:5000',
        ];
    }

    public function messages(): array
    {
        return [
            'title.required' => 'Title is required',
            'title.min' => 'Title must be at least 3 characters',
            'title.max' => 'Title cannot exceed 255 characters',
            'content.required' => 'Content is required',
            'content.min' => 'Content must be at least 10 characters',
            'content.max' => 'Content cannot exceed 5000 characters',
        ];
    }
}
