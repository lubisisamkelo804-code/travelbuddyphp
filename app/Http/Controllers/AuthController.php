<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Password as PasswordRule;
use Laravel\Socialite\Facades\Socialite;

class AuthController extends Controller
{
    public function showLogin() { return view('auth.login'); }

    public function login(Request $r)
    {
        $data = $r->validate(['email' => 'required', 'password' => 'required']);

        $user = User::where('email', $data['email'])->orWhere('username', $data['email'])->first();

        if (! $user || ! Hash::check($data['password'], $user->password)) {
            return back()->withErrors(['email' => 'Invalid credentials.'])->onlyInput('email');
        }

        Auth::login($user, $r->boolean('remember'));
        $r->session()->regenerate();

        return redirect()->intended(route('home'));
    }

    public function showRegister() { return view('auth.register'); }

    /* ---------- FORGOT / RESET PASSWORD ---------- */

    public function showForgotPassword() { return view('auth.forgot-password'); }

    public function sendResetLink(Request $r)
    {
        $r->validate(['email' => 'required|email']);

        $status = Password::sendResetLink($r->only('email'));

        return $status === Password::RESET_LINK_SENT
            ? back()->with('success', __($status))
            : back()->withErrors(['email' => __($status)])->onlyInput('email');
    }

    public function showResetPassword(Request $r, string $token)
    {
        return view('auth.reset-password', ['token' => $token, 'email' => $r->input('email')]);
    }

    public function resetPassword(Request $r)
    {
        $r->validate([
            'token' => 'required',
            'email' => 'required|email',
            'password' => ['required', 'confirmed', PasswordRule::min(8)->letters()->numbers()],
        ]);

        $status = Password::reset(
            $r->only('email', 'password', 'password_confirmation', 'token'),
            function (User $user, string $password) {
                $user->forceFill([
                    'password' => Hash::make($password),
                    'remember_token' => Str::random(60),
                ])->save();

                event(new PasswordReset($user));
            }
        );

        return $status === Password::PASSWORD_RESET
            ? redirect()->route('login')->with('success', __($status))
            : back()->withErrors(['email' => __($status)])->onlyInput('email');
    }

    public function register(Request $r)
    {
        $data = $r->validate([
            'name' => 'required|string|min:2|max:100',
            'username' => 'required|string|min:3|max:50|alpha_dash|unique:users',
            'email' => 'required|email|unique:users',
            'password' => ['required', 'confirmed', PasswordRule::min(8)->letters()->numbers()],
            'role' => 'required|in:visitor,destination_owner,event_creator',
        ]);

        $user = User::create([
            'name' => $data['name'], 'username' => $data['username'], 'email' => $data['email'],
            'password' => $data['password'], 'role' => $data['role'],
        ]);

        Auth::login($user);
        $r->session()->regenerate();

        return redirect(route('home'))->with('success', 'Welcome to TravelBuddy, ' . $user->name . '!');
    }

    public function logout(Request $r)
    {
        Auth::logout();
        $r->session()->invalidate();
        $r->session()->regenerateToken();
        return redirect(route('login'))->with('success', 'Logged out.');
    }

    public function google()
    {
        if (! config('services.google.client_id')) {
            return back()->with('error', 'Google OAuth is not configured yet.');
        }
        return Socialite::driver('google')->redirect();
    }

    public function googleCallback()
    {
        if (! config('services.google.client_id')) return redirect(route('login'));

        $g = Socialite::driver('google')->user();
        $user = User::where('google_id', $g->id)->orWhere('email', $g->email)->first();

        if (! $user) {
            $user = User::create([
                'name' => $g->name, 'email' => $g->email,
                'username' => str($g->email)->before('@')->append('_' . random_int(100, 999))->toString(),
                'password' => str()->random(32),
                'google_id' => $g->id, 'avatar_url' => $g->avatar,
            ]);
        }

        Auth::login($user, true);
        return redirect(route('home'))->with('success', 'Signed in with Google.');
    }
}