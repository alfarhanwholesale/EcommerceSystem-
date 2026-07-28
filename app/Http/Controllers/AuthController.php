<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Password;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Foundation\Auth\EmailVerificationRequest;

class AuthController extends Controller
{
    // Customer Register View
    public function showRegisterForm()
    {
        return view('auth.register');
    }

    // Customer Register Process
    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:6|confirmed',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        $user = new User([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
        ]);
        $user->role = 'customer';
        $user->save();

        event(new \Illuminate\Auth\Events\Registered($user));

        Auth::login($user);

        return redirect()->route('verification.notice')->with('success', 'Registration successful! Please verify your email.');
    }

    // Customer Login View
    public function showLoginForm()
    {
        return view('auth.login');
    }

    // Customer Login Process
    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        if (Auth::attempt($credentials)) {
            $request->session()->regenerate();

            $user = Auth::user();

            // Merge guest cart into user's DB cart on login
            if ($user->role === 'customer') {
                CartController::mergeGuestCart($user->id);
                return redirect()->intended(route('shop.home'))->with('success', 'Log masuk berjaya!');
            } else {
                // If staff attempts customer login, redirect to admin dashboard anyway
                return redirect()->route('admin.dashboard')->with('success', 'Staff logged in successfully.');
            }
        }

        return back()->withErrors([
            'email' => 'The provided credentials do not match our records.',
        ])->onlyInput('email');
    }

    // Admin Login View
    public function showAdminLoginForm()
    {
        return view('auth.admin-login');
    }

    // Admin Login Process
    public function adminLogin(Request $request)
    {
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        if (Auth::attempt($credentials)) {
            $user = Auth::user();

            if (in_array($user->role, ['admin', 'outdoor_sales', 'purchaser', 'storekeeper'])) {
                $request->session()->regenerate();
                return redirect()->route('admin.dashboard')->with('success', 'Welcome back, ' . $user->name);
            }

            Auth::logout();
            return back()->withErrors([
                'email' => 'Access denied. Only administrative users can log in here.',
            ])->onlyInput('email');
        }

        return back()->withErrors([
            'email' => 'The provided credentials do not match our records.',
        ])->onlyInput('email');
    }

    // Logout Process
    public function logout(Request $request)
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('shop.home')->with('success', 'Logged out successfully.');
    }

    // Google Sign-In with Firebase ID Token
    public function loginWithGoogle(Request $request)
    {
        $request->validate([
            'id_token' => 'required|string',
        ]);

        $idToken = $request->id_token;
        $apiKey = 'AIzaSyA-EDxariyRsE0ErsdVWlv3N2RJ5G28l00';

        // Verify token with Firebase REST API
        $response = Http::post("https://identitytoolkit.googleapis.com/v1/accounts:lookup?key={$apiKey}", [
            'idToken' => $idToken,
        ]);

        if ($response->failed()) {
            return redirect()->route('login')->with('error', 'Authentication failed. Invalid token.');
        }

        $data = $response->json();

        if (empty($data['users'][0])) {
            return redirect()->route('login')->with('error', 'Authentication failed. User not found in token.');
        }

        $firebaseUser = $data['users'][0];
        $email = $firebaseUser['email'] ?? null;
        $name = $firebaseUser['displayName'] ?? 'Google User';

        if (!$email) {
            return redirect()->route('login')->with('error', 'Authentication failed. Email not provided by Google.');
        }

        // Find or create user
        $user = User::where('email', $email)->first();

        if (!$user) {
            // Create user
            $user = new User([
                'name' => $name,
                'email' => $email,
                'password' => Hash::make(Str::random(24)),
            ]);
            $user->role = 'customer';
            $user->save();
        }

        // Log the user in
        Auth::login($user, true);

        $request->session()->regenerate();

        if ($user->role === 'customer') {
            CartController::mergeGuestCart($user->id);
            return redirect()->intended(route('shop.home'))->with('success', 'Log masuk berjaya melalui Google!');
        } else {
            return redirect()->route('admin.dashboard')->with('success', 'Staff logged in successfully via Google.');
        }
    }

    // Forgot Password - Show Form
    public function showForgotPasswordForm()
    {
        return view('auth.forgot-password');
    }

    // Forgot Password - Send Link Email
    public function sendResetLinkEmail(Request $request)
    {
        $request->validate(['email' => 'required|email']);

        $status = Password::sendResetLink(
            $request->only('email')
        );

        return $status === Password::RESET_LINK_SENT
                    ? back()->with('status', __($status))
                    : back()->withErrors(['email' => __($status)]);
    }

    // Reset Password - Show Form
    public function showResetPasswordForm(Request $request, $token)
    {
        return view('auth.reset-password', ['token' => $token, 'request' => $request]);
    }

    // Reset Password - Handle Update
    public function resetPassword(Request $request)
    {
        $request->validate([
            'token' => 'required',
            'email' => 'required|email',
            'password' => 'required|min:6|confirmed',
        ]);

        $status = Password::reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function ($user, $password) {
                $user->forceFill([
                    'password' => Hash::make($password)
                ])->setRememberToken(Str::random(60));

                $user->save();

                event(new PasswordReset($user));
            }
        );

        return $status === Password::PASSWORD_RESET
                    ? redirect()->route('login')->with('success', __($status))
                    : back()->withErrors(['email' => __($status)]);
    }

    // Email Verification - Show Notice Page
    public function showVerificationNotice(Request $request)
    {
        return $request->user()->hasVerifiedEmail()
                    ? redirect()->intended(route('shop.home'))
                    : view('auth.verify-email');
    }

    // Email Verification - Verify Signature
    public function verifyEmail(EmailVerificationRequest $request)
    {
        $request->fulfill();

        return redirect()->route('shop.home')->with('success', 'Email verified successfully! You can now shop.');
    }

    // Email Verification - Resend link
    public function resendVerificationEmail(Request $request)
    {
        $request->user()->sendEmailVerificationNotification();

        return back()->with('status', 'verification-link-sent');
    }
}
