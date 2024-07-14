<?php

namespace App\Http\Controllers\Auth;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Http\JsonResponse;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Http\Requests\Auth\LoginRequest;
use Illuminate\Support\Facades\Log;

class AuthenticatedSessionController extends Controller
{
    /**
     * Handle an incoming authentication request.
     */
    public function store(LoginRequest $request): JsonResponse
    {
        Log::info('Processing login request for user', [
            'phone_number' => $request->input('phone_number')
        ]);

        if ($request->authenticate()) {
            $user = $request->authenticate();
            // $token = $user->createToken('auth-token')->plainTextToken;

            $roles = $user->getRoleNames();

            Log::info('Login successful for user', [
                'phone_number' => $request->input('phone_number')
            ]);

            return response()->json([
                'message'=> 'User Logged in Successfully',
                'user' => $user,
                'type' => $roles[0],
                // 'token' => $token,
            ], 201);
        }

        Log::warning('Invalid credentials provided for user', [
            'phone_number' => $request->input('phone_number')
        ]);

        return response()->json([
            'message' => 'Invalid credentials',
        ], 401);
    }

    /**
     * Destroy an authenticated session.
     */
    public function destroy(Request $request): Response
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return response()->noContent();
    }
}
