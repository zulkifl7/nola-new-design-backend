<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
            'device_name' => ['nullable', 'string'],
        ]);

        $user = User::where('email', $request->input('email'))->first();
        if (!$user || !Hash::check($request->input('password'), $user->password)) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid credentials',
                'data' => null,
                'errors' => (object)[],
            ], 401);
        }
        $device = $request->input('device_name', 'api');
        $token = $user->createToken($device)->plainTextToken;

        return response()->json([
            'success' => true,
            'message' => 'OK',
            'data' => [
                'token' => $token,
                'user' => $user,
            ],
            'errors' => (object)[],
        ]);
    }

    public function logout(Request $request)
    {
        $user = $request->user();
        if ($user) {
            $user->currentAccessToken()?->delete();
        }
        return response()->json([
            'success' => true,
            'message' => 'Logged out',
            'data' => null,
            'errors' => (object)[],
        ]);
    }
}
