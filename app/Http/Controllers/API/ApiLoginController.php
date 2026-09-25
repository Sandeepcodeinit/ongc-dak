<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ApiLoginController extends Controller
{
    public function login(Request $request)
    {
        $loginId = trim((string) $request->input('emailid', $request->input('email', '')));
        $isEmail = filter_var($loginId, FILTER_VALIDATE_EMAIL) !== false;

        if ($isEmail) {
            $loginId = strtolower($loginId);
        }

        $password = (string) $request->input('password', '');

        if ($loginId === '' || $password === '') {
            return response()->json([
                'status' => 422,
                'message' => 'Email/CPF and password are required.',
            ], 422);
        }

        try {
            $response = Http::withToken(config('services.user_check.token'))
                ->acceptJson()
                ->post(config('services.user_check.url'), [
                    'username' => $loginId,
                    'password' => $password,
                ]);
        } catch (\Throwable $e) {
            return response()->json([
                'status' => 500,
                'message' => 'Login service unavailable. Please try again later.',
            ], 503);
        }

        $payload = $response->json();

        Log::info('user_check API response (API login)', [
            'status' => $response->status(),
            'body' => $payload,
        ]);

        if (! $response->successful() || (int) data_get($payload, 'status') !== 1) {
            return response()->json([
                'status' => 401,
                'message' => data_get($payload, 'message', 'Invalid email/CPF or password.'),
            ], 401);
        }

        $remoteUser = data_get($payload, 'data', []);

        if (empty($remoteUser)) {
            return response()->json([
                'status' => 401,
                'message' => 'Invalid email/CPF or password.',
            ], 401);
        }

        $userType = (int) data_get($remoteUser, 'user_type', 0);

        $userPayload = [
            'id' => data_get($remoteUser, 'id'),
            'name' => data_get($remoteUser, 'name', 'User'),
            'email' => data_get($remoteUser, 'email', $isEmail ? $loginId : ''),
            'cpf_no' => data_get($remoteUser, 'cpf_no'),
            'user_type' => $userType,
            'role_name' => data_get($remoteUser, 'role_name'),
        ];

        if ($userType > 0) {
            $userPayload['designation'] = data_get($remoteUser, 'designation');
            $userPayload['wc_name'] = data_get($remoteUser, 'wc_name');
        }

        return response()->json([
            'status' => 200,
            'message' => 'Login successful.',
            'user' => $userPayload,
            'token' => data_get($payload, 'token'),
            'expires_at' => Carbon::now()->addHours(12)->toDateTimeString(),
        ]);
    }
}