<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Interfaces\AuthRepositoryInterface;
use App\Http\Requests\Auth\LoginRequest;
use Throwable;

class AuthController extends Controller
{
    protected $authRepo;

    public function __construct(AuthRepositoryInterface $authRepo)
    {
        $this->authRepo = $authRepo;
    }

    public function login(LoginRequest $request)
    {
        try {
            $result = $this->authRepo->login($request->validated());
            return response()->success($result, 'Login berhasil');
        } catch (Throwable $e) {
            throw $e;
        }
    }

    public function me()
    {
        try {
            $user = $this->authRepo->getProfile();
            return response()->success($user, 'Data profil berhasil diambil');
        } catch (Throwable $e) {
            throw $e;
        }
    }

    public function logout()
    {
        try {
            $this->authRepo->logout();
            return response()->success(null, 'Logout berhasil');
        } catch (Throwable $e) {
            throw $e;
        }
    }
}
