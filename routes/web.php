<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use App\Models\User;

// If a `welcome` view exists, serve it at the root so the welcome page logic is reachable.
Route::get('/', function () {
    return view('welcome');
});

// SPA Routes
Route::get('/dashboard', function () {
    return view('app');
});

// Catch-all route for SPA
Route::get('/{any}', function () {
    return view('app');
})->where('any', '.*');

// Simple registration endpoint for creating the first users.
Route::post('/register', function (Request $request) {
    // Basic validation
    $request->validate([
        'name' => ['required', 'string', 'max:255'],
        'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
        'password' => ['required', 'string', 'min:6', 'confirmed'],
    ]);

    // Validate role
    if (!in_array($request->role, ['manager', 'employee'])) {
        return back()->withErrors(['role' => 'Invalid role selected']);
    }

    $user = User::create([
        'name' => $request->name,
        'email' => $request->email,
        'password' => Hash::make($request->password),
        'role' => $request->role
    ]);

    auth()->login($user);

    return redirect('/dashboard');
});

// Lightweight login endpoint that returns JSON when requested by AJAX/fetch
Route::post('/login', function (Request $request) {
    $credentials = $request->validate([
        'email' => ['required', 'email'],
        'password' => ['required'],
    ]);

    if (Auth::attempt($credentials)) {
        $request->session()->regenerate();

        if ($request->expectsJson()) {
            return response()->json([
                'status' => 'ok',
                'redirect' => url('/dashboard'),
            ]);
        }

        return redirect()->intended('/dashboard');
    }

    if ($request->expectsJson()) {
        return response()->json([
            'status' => 'error',
            'message' => 'The provided credentials do not match our records.',
        ], 422);
    }

    return back()->withErrors([
        'email' => 'The provided credentials do not match our records.',
    ]);
});

// Keep existing catch-all route for SPA behavior
// Simple dashboard to validate login — lightweight HTML response so no new view files are required.
Route::get('/dashboard', function (Request $request) {
    if (! auth()->check()) {
        return redirect('/');
    }

    $user = auth()->user();

    $html = '<!doctype html><html><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>Dashboard</title></head><body style="font-family:Arial,Helvetica,sans-serif;padding:24px;">';
    $html .= '<h1>Dashboard</h1>';
    $html .= '<p>Welcome, ' . e($user->name) . '</p>';
    $html .= '<p><a href="' . url('/') . '">Home</a> | <a href="' . url('/logout') . '">Logout</a></p>';
    $html .= '</body></html>';

    return response($html);
});

// Logout route (GET for convenience in this lightweight setup)
Route::get('/logout', function (Request $request) {
    Auth::logout();
    $request->session()->invalidate();
    $request->session()->regenerateToken();
    return redirect('/');
});

Route::get('/{any}', function () {
    return view('app');
})->where('any', '.*');
