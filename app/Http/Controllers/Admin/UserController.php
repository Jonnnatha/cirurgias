<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Inertia\Inertia;

class UserController extends Controller
{
    public function create()
    {
        return Inertia::render('Admin/CreateUser');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'nome' => ['required', 'string', 'max:255'],
            'senha' => ['required', 'confirmed'],
            'hierarquia' => ['required', 'in:enfermeiro,medico'],
        ]);

        $user = User::create($data);
        $user->assignRole($request->hierarquia);

        return redirect()->route('admin.dashboard')->with('success', 'Usuário criado com sucesso.');
    }
}
