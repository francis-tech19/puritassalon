<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\Customer;
use App\Models\Role;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class AuthController extends Controller
{
    public function showLogin()
    {
        if (Auth::check()) {
            return $this->redirectBasedOnRole(Auth::user());
        }

        return view('auth.login');
    }

    public function showRegister()
    {
        return view('auth.register');
    }

    public function register(Request $request)
    {
        $validated = $request->validate([
            'full_name' => 'required|string|max:100',
            'email' => 'required|email|max:100|unique:users,email',
            'phone' => 'required|string|max:20',
            'password' => ['required', 'string', 'min:8', 'confirmed', 'regex:/[A-Z]/', 'regex:/[0-9]/'],
        ]);

        $customer = Customer::create([
            'customer_code' => 'CUST-'.strtoupper(Str::random(6)),
            'full_name' => $validated['full_name'],
            'phone' => $validated['phone'],
            'email' => $validated['email'],
            'status' => 'ACTIVE',
        ]);
        $code = (string) random_int(100000, 999999);
        $user = User::create([
            'username' => $validated['email'],
            'name' => $validated['full_name'],
            'email' => $validated['email'],
            'password' => $validated['password'],
            'role_id' => Role::firstOrCreate(['role_name' => 'CUSTOMER'])->id,
            'customer_id' => $customer->id,
            'is_active' => true,
            'verification_code' => $code,
            'verification_expires_at' => now()->addMinutes(10),
        ]);

        $request->session()->put('booking_intent', array_filter([
            'service' => $request->input('service'),
            'return' => $request->input('return'),
        ]));

        Mail::raw("Your Purita's Beauty Lounge verification code is {$code}. It expires in 10 minutes.", function ($message) use ($user): void {
            $message->to($user->email)->subject('Verify your salon account');
        });

        return redirect()->route('verification.form', $user)->with('success', 'A verification code was sent to your email.');
    }

    public function showVerification(User $user)
    {
        return view('auth.verify', compact('user'));
    }

    public function verify(Request $request, User $user)
    {
        $request->validate(['code' => 'required|digits:6']);
        if ($user->email_verified_at || $user->verification_code !== $request->code || ! $user->verification_expires_at || now()->greaterThan($user->verification_expires_at)) {
            return back()->withErrors(['code' => 'The verification code is invalid or expired.']);
        }
        $user->update(['email_verified_at' => now(), 'verification_code' => null, 'verification_expires_at' => null]);
        Auth::login($user);
        $request->session()->regenerate();

        return redirect()->route('customer.dashboard');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'username' => 'required|string',
            'password' => 'required|string',
        ]);

        $user = User::where('username', $credentials['username'])->first();

        if (! $user) {
            AuditLog::log(null, 'LOGIN_FAILED', 'AUTHENTICATION', 'Failed login attempt for an unknown username.');

            return back()->withErrors(['username' => 'Invalid username or password.'])->withInput();
        }

        if (! $user->is_active) {
            AuditLog::log($user->id, 'LOGIN_BLOCKED', 'AUTHENTICATION', 'Login blocked for an inactive account.');

            return back()->withErrors(['username' => 'Invalid username or password.'])->withInput();
        }

        if ($user->isCustomer() && ! $user->email_verified_at) {
            return back()->withErrors(['username' => 'Please verify your email before logging in.']);
        }

        if (Auth::attempt(['username' => $credentials['username'], 'password' => $credentials['password']], $request->filled('remember'))) {
            $request->session()->regenerate();

            if ($user->isCustomer() && ($request->filled('service') || $request->filled('return'))) {
                $request->session()->put('booking_intent', array_filter([
                    'service' => $request->input('service'),
                    'return' => $request->input('return'),
                ]));
            }

            AuditLog::log(Auth::id(), 'LOGIN', 'AUTHENTICATION', "User {$user->username} logged in successfully via web.");

            if ($request->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'user' => $user->load('role', 'employee'),
                    'redirect' => $this->getRedirectPath($user),
                ]);
            }

            return $this->redirectBasedOnRole($user);
        }

        AuditLog::log($user->id, 'LOGIN_FAILED', 'AUTHENTICATION', 'Failed login attempt with an invalid password.');

        return back()->withErrors(['username' => 'Invalid username or password.'])->withInput();
    }

    public function logout(Request $request)
    {
        if (Auth::check()) {
            AuditLog::log(Auth::id(), 'LOGOUT', 'AUTHENTICATION', 'User '.Auth::user()->username.' logged out.');
        }

        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        if ($request->wantsJson()) {
            return response()->json(['success' => true, 'message' => 'Logged out successfully']);
        }

        return redirect()->route('login')->with('success', 'Logged out successfully.');
    }

    public function me(Request $request)
    {
        $user = User::with(['role', 'employee'])->findOrFail(Auth::id());

        return response()->json([
            'success' => true,
            'user' => $user,
        ]);
    }

    private function redirectBasedOnRole(User $user)
    {
        return redirect()->to($this->getRedirectPath($user));
    }

    private function getRedirectPath(User $user): string
    {
        if ($user->isAdmin()) {
            return route('admin.dashboard');
        }
        if ($user->isOwner()) {
            return route('dashboard');
        }
        if ($user->isCustomer()) {
            return route('customer.dashboard');
        }
        if ($user->isStaff()) {
            return route('employee.dashboard');
        }

        return route('appointments.index');
    }
}
