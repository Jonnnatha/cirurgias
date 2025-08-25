<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Providers\RouteServiceProvider;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class EmailVerificationNotificationController extends Controller
{
    /**
     * Handle a verification request without email.
     */
    public function store(Request $request): RedirectResponse
    {
        return redirect()->intended(RouteServiceProvider::HOME);
    }
}
