<?php

namespace App\Repositories;

use App\Enums\UserRole;
use App\Http\Resources\UserResource;
use App\Interfaces\AuthRepositoryInterface;
use App\Models\User;
use Exception;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthRepository implements AuthRepositoryInterface
{
    public function login(array $credentials): array
    {
        $user = User::where('username', $credentials['username'])->where('role', UserRole::STUDENT)->first();

        if (!$user || !Hash::check($credentials['password'], $user->password)) {
            throw ValidationException::withMessages([
                'username' => ['Kredensial yang diberikan salah.'],
            ]);
        }

        if ($user->tokens()->exists()) {
            throw new Exception('Akun Anda sedang aktif di perangkat lain. Silakan logout terlebih dahulu.');
        }

        $user->tokens()->delete();

        $token = $user->createToken('auth_token_mobile')->plainTextToken;

        return [
            'user' => new UserResource($user->load(['student.classroom.major'])),
            'access_token' => $token,
            'token_type' => 'Bearer',
        ];
    }

    public function getProfile(): UserResource
    {
        $user = Auth::guard('api')->user();
        if (!$user) {
            throw new Exception("Sesi berakhir, silakan login kembali");
        }

        return new UserResource($user->load('student.classroom.major'));
    }

    public function logout(): bool
    {
        $user = Auth::guard('api')->user();
        if ($user) {
            return $user->currentAccessToken()->delete();
        }
        return false;
    }
}
