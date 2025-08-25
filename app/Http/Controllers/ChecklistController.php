<?php

namespace App\Http\Controllers;

use App\Models\Checklist;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ChecklistController extends Controller
{
    public function index()
    {
        $this->authorize('viewAny', Checklist::class);

        $checklists = Checklist::query()->latest()->get();

        return inertia('Checklists/Index', [
            'checklists' => $checklists,
        ]);
    }

    public function create()
    {
        $this->authorize('create', Checklist::class);

        return inertia('Checklists/Create');
    }

    public function store(Request $request)
    {
        $this->authorize('create', Checklist::class);

        $data = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'active' => ['boolean'],
        ]);

        Checklist::create([
            'title' => $data['title'],
            'active' => $data['active'] ?? false,
            'created_by' => Auth::id(),
        ]);

        return redirect()->route('checklists.index')->with('ok', 'Checklist criada!');
    }

    public function edit(Checklist $checklist)
    {
        $this->authorize('update', $checklist);

        return inertia('Checklists/Edit', [
            'checklist' => $checklist,
        ]);
    }

    public function update(Request $request, Checklist $checklist)
    {
        $this->authorize('update', $checklist);

        $data = $request->validate([
            'title' => ['sometimes', 'required', 'string', 'max:255'],
            'active' => ['sometimes', 'boolean'],
        ]);

        $checklist->update($data);

        return redirect()->route('checklists.index')->with('ok', 'Checklist atualizada!');
    }

    public function destroy(Checklist $checklist)
    {
        $this->authorize('delete', $checklist);

        $checklist->delete();

        return redirect()->route('checklists.index')->with('ok', 'Checklist removida!');
    }
}
