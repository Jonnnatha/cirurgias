<?php

namespace App\Policies;

use App\Models\Checklist;
use App\Models\User;

class ChecklistPolicy
{
    public function viewAny(User $u): bool
    {
        return $u->hasAnyRole(['admin','enfermeiro']);
    }

    public function view(User $u, Checklist $c): bool
    {
        return $u->hasAnyRole(['admin','enfermeiro']);
    }

    public function create(User $u): bool
    {
        return $u->hasAnyRole(['admin','enfermeiro']);
    }

    public function update(User $u, Checklist $c): bool
    {
        return $u->hasAnyRole(['admin','enfermeiro']);
    }

    public function delete(User $u, Checklist $c): bool
    {
        return $u->hasRole('admin'); // se quiser permitir enfermeiro, troque para hasAnyRole
    }
}
