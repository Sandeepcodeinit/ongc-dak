<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\View\View;

class LoginController extends Controller
{
    public function showLoginForm(): View
    {
        if (session('user')) {
            return redirect()->route('dashboard');
        }

        return view('login');
    }

    public function login(Request $request)
    {
        $loginId = trim((string) $request->input('emailid', $request->input('email', '')));
        $isEmail = filter_var($loginId, FILTER_VALIDATE_EMAIL) !== false;

        if ($isEmail) {
            $loginId = strtolower($loginId);
        }

        $password = $request->input('password');

        $validator = Validator::make($request->all() + ['login_id' => $loginId], [
            'login_id' => ['required', 'string'],
            'password' => ['required', 'string'],
        ], [
            'login_id.required' => 'Email or CPF number is required.',
            'password.required' => 'Password is required.',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        try {
            $response = Http::withToken(config('services.user_check.token'))
                ->acceptJson()
                ->post(config('services.user_check.url'), [
                    'username' => $loginId,
                    'password' => $password,
                ]);
        } catch (\Throwable $e) {
            return back()->withErrors([
                'email' => 'Login service unavailable. Please try again later.',
            ])->withInput();
        }

        $payload = $response->json();

        Log::info('user_check API response (CSR login)', [
            'status' => $response->status(),
            'body' => $payload,
        ]);

        if (! $response->successful() || (int) data_get($payload, 'status') !== 1) {
            return back()->withErrors([
                'email' => data_get($payload, 'message', 'Invalid Email/CPF or password.'),
            ])->withInput();
        }

        $remoteUser = data_get($payload, 'data', []);

        if (empty($remoteUser)) {
            return back()->withErrors([
                'email' => 'Invalid Email/CPF or password.',
            ])->withInput();
        }

        $userType = (int) data_get($remoteUser, 'user_type', 0);

        $sessionUser = [
            'id' => data_get($remoteUser, 'id'),
            'name' => data_get($remoteUser, 'name', 'User'),
            'user_name' => data_get($remoteUser, 'name', 'User'),
            'email' => data_get($remoteUser, 'email', $isEmail ? $loginId : ''),
            'cpf_no' => data_get($remoteUser, 'cpf_no'),
            'user_type' => $userType,
            'role_name' => data_get($remoteUser, 'role_name'),
        ];

        if ($userType > 0) {
            $sessionUser['designation'] = data_get($remoteUser, 'designation');
            $sessionUser['wc_name'] = data_get($remoteUser, 'wc_name');
            $sessionUser['work_center_name'] = data_get($remoteUser, 'wc_name');
        }

        session([
            'user' => $sessionUser,
            'user_id' => data_get($remoteUser, 'id'),
        ]);

        return redirect()->route('hard_copy_proposal');
    }

    public function dashboard()
    {
        if (! session('user')) {
            return redirect()->route('login');
        }

        $user = session('user');

        return view('dashboard', [
            'user' => $user,
            'workCenterNames' => DB::table('addresstos')->where('status', 1)->get(),
            'workCenter' => data_get($user, 'work_station', null),
        ]);
    }

    public function logout(Request $request)
    {
        $request->session()->forget('user');
        $request->session()->forget('user_id');
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }
}