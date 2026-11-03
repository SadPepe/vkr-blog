<?php

namespace App\Http\Requests;

use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ProfileUpdateRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => [
                'required',
                'string',
                'lowercase',
                'email',
                'max:255',
                Rule::unique(User::class)->ignore($this->user()->id),
            ],
            'about' => ['nullable', 'string', 'max:1000'],
            'cover_image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
            'background_image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
            'remove_cover_image' => ['nullable', 'boolean'],
            'remove_background_image' => ['nullable', 'boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'cover_image.image' => 'Обложка должна быть изображением.',
            'cover_image.mimes' => 'Обложка: допустимы JPG, PNG или WebP.',
            'cover_image.max' => 'Обложка не должна быть больше 5 МБ.',
            'background_image.image' => 'Фон должен быть изображением.',
            'background_image.mimes' => 'Фон: допустимы JPG, PNG или WebP.',
            'background_image.max' => 'Фон не должен быть больше 5 МБ.',
        ];
    }
}
