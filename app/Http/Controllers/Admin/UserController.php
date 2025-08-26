<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Inertia\Inertia;

class UserController extends Controller
{
    public function index()
    {
        $users = User::role(['enfermeiro', 'medico'])->get();

        return Inertia::render('Admin/Users/Index', [
            'users' => $users,
        ]);
    }

    public function edit(User $user)
    {
        return Inertia::render('Admin/Users/Edit', [
            'user' => $user,
        ]);
    }

    public function update(Request $request, User $user)
    {
        $data = $request->validate([
            'nome' => ['required', 'string', 'max:255'],
            'hierarquia' => ['required', 'in:enfermeiro,medico'],
            'senha' => ['nullable', 'confirmed'],
        ]);

        $user->nome = $data['nome'];
        $user->hierarquia = $data['hierarquia'];
        if (!empty($data['senha'])) {
            $user->senha = $data['senha'];
        }
        $user->save();
        $user->syncRoles($data['hierarquia']);

        return redirect()->route('admin.users.index')
            ->with('success', 'Usuário atualizado com sucesso.');
    }

    public function destroy(User $user)
    {
        $user->delete();

        return redirect()->route('admin.users.index')
            ->with('success', 'Usuário removido com sucesso.');
    }

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
