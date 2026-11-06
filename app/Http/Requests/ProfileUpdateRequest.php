<?php

namespace App\Http\Requests;

use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;

class ProfileUpdateRequest extends FormRequest
{
    /**
     * Email необязателен: приводим к нижнему регистру и пустую строку — к null.
     */
    protected function prepareForValidation(): void
    {
        if ($this->has('email')) {
            $this->merge([
                'email' => $this->filled('email')
                    ? Str::lower(trim((string) $this->input('email')))
                    : null,
            ]);
        }
    }

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
                'nullable',
                'string',
                'email',
                'max:255',
                function (string $attribute, mixed $value, \Closure $fail) {
                    if ($value !== null && User::where('email', $value)
                        ->where('id', '!=', $this->user()->id)
                        ->exists()) {
                        $fail('Этот email уже используется.');
                    }
                },
            ],
            'about' => ['nullable', 'string', 'max:1000'],
            'avatar_image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            'cover_image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
            'background_image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
            'background_blur' => ['nullable', 'integer', 'between:0,24'],
            'links' => ['nullable', 'array', 'max:5'],
            'links.*.label' => ['nullable', 'string', 'max:40'],
            'links.*.url' => ['nullable', 'string', 'max:255'],
            'remove_avatar_image' => ['nullable', 'boolean'],
            'remove_cover_image' => ['nullable', 'boolean'],
            'remove_background_image' => ['nullable', 'boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'avatar_image.image' => 'Аватар должен быть изображением.',
            'avatar_image.mimes' => 'Аватар: допустимы JPG, PNG или WebP.',
            'avatar_image.max' => 'Аватар не должен быть больше 2 МБ.',
            'cover_image.image' => 'Обложка должна быть изображением.',
            'cover_image.mimes' => 'Обложка: допустимы JPG, PNG или WebP.',
            'cover_image.max' => 'Обложка не должна быть больше 5 МБ.',
            'background_image.image' => 'Фон должен быть изображением.',
            'background_image.mimes' => 'Фон: допустимы JPG, PNG или WebP.',
            'background_image.max' => 'Фон не должен быть больше 5 МБ.',
            'background_blur.integer' => 'Размытие фона должно быть числом.',
            'background_blur.between' => 'Размытие фона: от 0 до 24 px.',
            'links.max' => 'Ссылок может быть не больше пяти.',
            'links.*.label.max' => 'Название ссылки — до 40 символов.',
            'links.*.url.max' => 'Адрес ссылки — до 255 символов.',
        ];
    }

    /**
     * Проверяем адрес после подстановки схемы: «t.me/name» — тоже валидная ссылка.
     */
    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            foreach ((array) $this->input('links', []) as $index => $link) {
                $url = trim((string) ($link['url'] ?? ''));

                if ($url === '') {
                    continue;
                }

                $normalized = preg_match('#^https?://#i', $url) ? $url : 'https://' . $url;

                if (!filter_var($normalized, FILTER_VALIDATE_URL)) {
                    $validator->errors()->add('links.' . $index . '.url', 'Ссылка должна быть адресом сайта.');
                }
            }
        });
    }
}
