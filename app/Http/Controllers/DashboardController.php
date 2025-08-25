<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;

class DashboardController extends Controller
{
    public function admin(Request $request)
    {
        return Inertia::render('Admin/Dashboard', [
            'user' => $request->user(),
        ]);
    }

    public function medico(Request $request)
    {
        return Inertia::render('Medico/Dashboard', [
            'user' => $request->user(),
        ]);
    }

    public function enfermeiro(Request $request)
    {
        return Inertia::render('Enfermeiro/Dashboard', [
            'user' => $request->user(),
        ]);
    }
}
