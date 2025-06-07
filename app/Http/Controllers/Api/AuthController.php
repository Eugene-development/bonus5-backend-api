<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\LoginRequest;
use App\Http\Requests\RegisterRequest;
use App\Http\Resources\UserResource;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Foundation\Auth\EmailVerificationRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    /**
     * Регистрация нового пользователя
     */
    public function register(RegisterRequest $request): JsonResponse
    {
        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'city' => $request->city,
            'password' => Hash::make($request->password),
        ]);

        // Отправляем событие Registered для автоматической отправки email verification
        event(new Registered($user));

        // Для SPA аутентификации регистрируем и логиним пользователя
        Auth::login($user, $request->boolean('remember_me', false));

        return response()->json([
            'message' => 'Пользователь успешно зарегистрирован. Проверьте email для верификации.',
            'user' => new UserResource($user),
            'email_verification_required' => !$user->hasVerifiedEmail(),
        ], 201);
    }

    /**
     * Вход пользователя в систему (SPA Authentication)
     */
    public function login(LoginRequest $request): JsonResponse
    {
        $credentials = $request->only('email', 'password');

        if (!Auth::attempt($credentials, $request->boolean('remember_me', false))) {
            throw ValidationException::withMessages([
                'email' => ['Неверные учетные данные.'],
            ]);
        }

        $user = Auth::user();

        return response()->json([
            'message' => 'Успешный вход в систему',
            'user' => new UserResource($user),
        ]);
    }

    /**
     * Выход пользователя из системы (SPA Authentication)
     */
    public function logout(Request $request): JsonResponse
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return response()->json([
            'message' => 'Успешный выход из системы',
        ]);
    }

    /**
     * Отправка письма с верификацией email
     */
    public function sendEmailVerification(Request $request): JsonResponse
    {
        $user = $request->user();

        if ($user->hasVerifiedEmail()) {
            return response()->json([
                'message' => 'Email уже подтвержден',
            ], 422);
        }

        $user->sendEmailVerificationNotification();

        return response()->json([
            'message' => 'Письмо с подтверждением отправлено',
        ]);
    }

    /**
     * Верификация email по токену
     */
    public function verifyEmail(EmailVerificationRequest $request): JsonResponse
    {
        $request->fulfill();

        return response()->json([
            'message' => 'Email успешно подтвержден',
            'user' => new UserResource($request->user()),
        ]);
    }

    /**
     * Повторная отправка письма с верификацией
     */
    public function resendEmailVerification(Request $request): JsonResponse
    {
        $user = $request->user();

        if ($user->hasVerifiedEmail()) {
            return response()->json([
                'message' => 'Email уже подтвержден',
            ], 422);
        }

        $user->sendEmailVerificationNotification();

        return response()->json([
            'message' => 'Письмо с подтверждением отправлено повторно',
        ]);
    }
}
