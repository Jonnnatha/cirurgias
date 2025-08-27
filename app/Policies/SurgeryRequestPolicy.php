<?php

namespace App\Policies;

use App\Models\SurgeryRequest;
use App\Models\User;

class SurgeryRequestPolicy
{
    // Qualquer admin ou enfermeiro vê tudo; médico vê os dele
    public function viewAny(User $u): bool
    {
        return $u->hasAnyRole(['admin', 'enfermeiro', 'medico']);
    }

    public function view(User $u, SurgeryRequest $r): bool
    {
        return $u->hasAnyRole(['admin','enfermeiro']) || ($u->hasRole('medico') && $r->doctor_id === $u->id);
    }

    // Criar pedido: admin e médico
    public function create(User $u): bool
    {
        return $u->hasAnyRole(['admin','medico']);
    }

    // Atualizar pedido:
    // - admin sempre pode (exceto sala/duração após aprovação)
    // - enfermeiro pode editar enquanto estiver requested/rejected (antes da aprovação final)
    // - médico só pode editar o próprio pedido enquanto estiver requested
    public function update(User $u, SurgeryRequest $r, array $data = []): bool
    {
        if ($r->status === 'approved') {
            $protected = ['room_number', 'duration_minutes'];
            foreach ($protected as $field) {
                if (array_key_exists($field, $data) && $data[$field] != $r->{$field}) {
                    return false;
                }
            }
        }

        if ($u->hasRole('admin')) return true;
        if ($u->hasRole('enfermeiro')) return in_array($r->status, ['requested','rejected'], true);
        if ($u->hasRole('medico')) return $r->doctor_id === $u->id && $r->status === 'requested';
        return false;
    }

    // Apagar pedido (se precisar)
    public function delete(User $u, SurgeryRequest $r): bool
    {
        if ($u->hasRole('admin')) return true;
        if ($u->hasRole('medico')) return $r->doctor_id === $u->id && $r->status === 'requested';
        return false;
    }

    // Aprovar (enfermeiro/admin) — só se estiver solicitado
    public function approve(User $u, SurgeryRequest $r): bool
    {
        return $u->hasAnyRole(['admin','enfermeiro']) && $r->status === 'requested';
    }

    // Reprovar (enfermeiro/admin) — só se estiver solicitado
    public function reject(User $u, SurgeryRequest $r): bool
    {
        return $u->hasAnyRole(['admin','enfermeiro']) && $r->status === 'requested';
    }

    // Marcar itens do checklist (enfermeiro/admin)
    public function markChecklist(User $u, SurgeryRequest $r): bool
    {
        return $u->hasAnyRole(['admin','enfermeiro']) && in_array($r->status, ['requested','rejected'], true);
    }
}
