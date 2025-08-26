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
        $users = User::whereIn('hierarquia', ['enfermeiro', 'medico'])
            ->get(['id', 'nome', 'hierarquia']);

        return Inertia::render('Admin/Index', [
            'users' => $users,
        ]);
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

        return redirect()->route('admin.users.index')->with('success', 'Usuário criado com sucesso.');
    }

    public function show(User $user)
    {
        return Inertia::render('Admin/Show', [
            'user' => $user->only(['id', 'nome', 'hierarquia']),
        ]);
    }

    public function edit(User $user)
    {
        return Inertia::render('Admin/Edit', [
            'user' => $user->only(['id', 'nome', 'hierarquia']),
        ]);
    }

    public function update(Request $request, User $user)
    {
        $data = $request->validate([
            'nome' => ['required', 'string', 'max:255'],
            'hierarquia' => ['required', 'in:enfermeiro,medico'],
            'senha' => ['nullable', 'confirmed'],
        ]);

        if (empty($data['senha'])) {
            unset($data['senha']);
        }

        $user->update($data);
        $user->syncRoles([$request->hierarquia]);

        return redirect()->route('admin.users.index')->with('success', 'Usuário atualizado com sucesso.');
    }

    public function destroy(User $user)
    {
        $user->delete();

        return redirect()->route('admin.users.index')->with('success', 'Usuário excluído com sucesso.');
    }
}
