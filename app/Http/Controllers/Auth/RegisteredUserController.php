<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules;
use Inertia\Inertia;
use Inertia\Response;

class RegisteredUserController extends Controller
{
    /**
     * Display the registration view.
     */
    public function create(): Response
    {
        return Inertia::render('Auth/Register');
    }

    /**
     * Handle an incoming registration request.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store(Request $request): RedirectResponse
    {
        $request->merge([
            'username' => Str::lower(trim((string) $request->input('username'))),
            'email' => $request->filled('email')
                ? Str::lower(trim((string) $request->input('email')))
                : null,
            'invite_code' => trim((string) $request->input('invite_code')),
        ]);

        $inviteCode = (string) config('auth.registration_invite_code');

        $request->validate([
            'name' => 'required|string|max:255',
            'username' => [
                'required',
                'string',
                'min:3',
                'max:32',
                'regex:/^[a-z0-9_]+$/',
                'unique:users,username',
            ],
            'email' => [
                'nullable',
                'string',
                'email',
                'max:255',
                function (string $attribute, mixed $value, \Closure $fail) {
                    if ($value !== null && User::where('email', $value)->exists()) {
                        $fail('Этот email уже используется.');
                    }
                },
            ],
            'invite_code' => $inviteCode !== ''
                ? ['required', 'string', Rule::in([$inviteCode])]
                : ['nullable'],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
            'accept_terms' => ['accepted'],
            'accept_privacy' => ['accepted'],
            'accept_distribution' => ['accepted'],
        ], [
            'username.required' => 'Придумайте логин.',
            'username.min' => 'Логин — не короче 3 символов.',
            'username.max' => 'Логин — не длиннее 32 символов.',
            'username.regex' => 'Логин: только латиница, цифры и подчёркивание.',
            'username.unique' => 'Этот логин уже занят.',
            'invite_code.required' => 'Введите код приглашения.',
            'invite_code.in' => 'Неверный код приглашения.',
            'accept_terms.accepted' => 'Примите пользовательское соглашение.',
            'accept_privacy.accepted' => 'Дайте согласие на обработку персональных данных.',
            'accept_distribution.accepted' => 'Подтвердите согласие на публикацию профиля.',
        ]);

        $user = User::create([
            'name' => $request->name,
            'username' => $request->username,
            'email' => $request->email,
            'password' => Hash::make($request->password),
        ]);

        // Фиксируем факт согласий: дата и версия документов (доказательство по 152-ФЗ).
        $user->forceFill([
            'terms_accepted_at' => now(),
            'terms_version' => config('legal.version'),
            'privacy_consent_at' => now(),
            'privacy_version' => config('legal.version'),
            'distribution_consent_at' => now(),
        ])->save();

        event(new Registered($user));

        Auth::login($user);

        return redirect(route('profile', absolute: false));
    }
}
