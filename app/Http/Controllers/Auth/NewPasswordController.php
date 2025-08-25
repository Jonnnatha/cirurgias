<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;

class NewPasswordController extends Controller
{
    /**
     * Display the password reset view.
     */
    public function create(Request $request): Response
    {
        return Inertia::render('Auth/ResetPassword', [
            'nome' => $request->nome,
            'token' => $request->route('token'),
        ]);
    }

    /**
     * Handle an incoming new password request.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'token' => 'required',
            'nome' => 'required',
            'senha' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        $record = DB::table('password_reset_tokens')
            ->where('email', $request->nome)
            ->first();

        if (! $record || $record->token !== $request->token) {
            throw ValidationException::withMessages([
                'nome' => [trans(Password::INVALID_TOKEN)],
            ]);
        }

        $user = \App\Models\User::where('nome', $request->nome)->first();

        if (! $user) {
            throw ValidationException::withMessages([
                'nome' => [trans(Password::INVALID_USER)],
            ]);
        }

        $user->forceFill([
            'senha' => Hash::make($request->senha),
            'remember_token' => Str::random(60),
        ])->save();

        event(new PasswordReset($user));

        DB::table('password_reset_tokens')->where('email', $request->nome)->delete();

        return redirect()->route('login')->with('status', __(Password::PASSWORD_RESET));
    }
}
