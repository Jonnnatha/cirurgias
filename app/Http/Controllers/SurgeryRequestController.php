<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreSurgeryRequestRequest;
use App\Models\SurgeryRequest;
use App\Models\SurgeryChecklistItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class SurgeryRequestController extends Controller
{
    // Lista do médico logado
    public function indexMy(Request $req)
    {
        $this->authorize('viewAny', SurgeryRequest::class);

        $data = SurgeryRequest::query()
            ->where('doctor_id', Auth::id())
            ->latest('date')->latest('start_time')
            ->paginate(15);

        $data->getCollection()->transform(function ($r) {
            $r->can_cancel = Auth::user()->can('delete', $r) || Auth::user()->can('update', $r);
            return $r;
        });

        return inertia('Medico/MinhasSolicitacoes', [
            'requests' => $data
        ]);
    }

    // Lista geral (para enfermagem/admin) com filtro por status
    public function index(Request $req)
    {
        $this->authorize('viewAny', SurgeryRequest::class);

        $q = SurgeryRequest::query()->with(['doctor','nurse'])
            ->when($req->status, fn($qq) => $qq->where('status', $req->status))
            ->orderBy('date')->orderBy('start_time');

        $requests = $q->paginate(20);
        $requests->getCollection()->transform(function ($r) {
            $r->can_cancel = Auth::user()->can('delete', $r) || Auth::user()->can('update', $r);
            return $r;
        });

        return inertia('Enfermeiro/Solicitacoes', [
            'requests' => $requests,
            'filters'  => ['status' => $req->status]
        ]);
    }

    // Formulário de criação (médico/admin)
    public function create()
    {
        $this->authorize('create', SurgeryRequest::class);

        return inertia('Medico/NovaSolicitacao');
    }

    // Formulário de edição
    public function edit(SurgeryRequest $requestModel)
    {
        $this->authorize('update', $requestModel);

        return inertia('Medico/NovaSolicitacao', [
            'request' => $requestModel
        ]);
    }

    // Criar pedido (médico/admin)
    public function store(StoreSurgeryRequestRequest $request)
    {
        $this->authorize('create', SurgeryRequest::class);

        $date  = $request->date;
        $start = $request->start_time;
        $end   = $request->end_time;

        return DB::transaction(function () use ($date, $start, $end, $request) {

            // 1) LOCK por dia (anti-corrida)
            DB::select('SELECT id FROM surgery_requests WHERE date = ? FOR UPDATE', [$date]);

            // 2) Checagem de sobreposição (global)
            $overlap = SurgeryRequest::where('date', $date)
                ->whereIn('status', ['requested','approved'])
                ->where('start_time', '<', $end)
                ->where('end_time', '>', $start)
                ->exists();

            if ($overlap) {
                throw ValidationException::withMessages([
                    'start_time' => 'Conflito de horário: já existe um agendamento que sobrepõe este intervalo.'
                ]);
            }

            // 3) Cria a solicitação
            $surgery = SurgeryRequest::create([
                'doctor_id'    => Auth::id(),
                'date'         => $date,
                'start_time'   => $start,
                'end_time'     => $end,
                'room_number'  => $request->room_number,
                'duration_minutes' => $request->duration_minutes,
                'patient_name' => $request->patient_name,
                'procedure'    => $request->procedure,
                'status'       => 'requested',
                'meta'         => ['confirm_docs' => (bool) $request->boolean('confirm_docs')],
            ]);

            return back()->with('ok', 'Solicitação criada!');
        });
    }

    // Atualizar pedido
    public function update(StoreSurgeryRequestRequest $request, SurgeryRequest $requestModel)
    {
        $this->authorize('update', $requestModel);

        $date  = $request->date;
        $start = $request->start_time;
        $end   = $request->end_time;

        return DB::transaction(function () use ($date, $start, $end, $request, $requestModel) {

            // LOCK por dia para evitar corrida
            DB::select('SELECT id FROM surgery_requests WHERE date = ? FOR UPDATE', [$date]);

            $overlap = SurgeryRequest::where('date', $date)
                ->where('id', '!=', $requestModel->id)
                ->whereIn('status', ['requested','approved'])
                ->where('start_time', '<', $end)
                ->where('end_time', '>', $start)
                ->exists();

            if ($overlap) {
                throw ValidationException::withMessages([
                    'start_time' => 'Conflito de horário: já existe um agendamento que sobrepõe este intervalo.'
                ]);
            }

            $requestModel->update([
                'date'         => $date,
                'start_time'   => $start,
                'end_time'     => $end,
                'room_number'  => $request->room_number,
                'duration_minutes' => $request->duration_minutes,
                'patient_name' => $request->patient_name,
                'procedure'    => $request->procedure,
                'meta'         => array_merge($requestModel->meta ?? [], [
                    'confirm_docs' => (bool) $request->boolean('confirm_docs'),
                ]),
            ]);

            return back()->with('ok', 'Solicitação atualizada!');
        });
    }

    // Atualizar item do checklist (enfermeiro/admin)
    public function updateChecklistItem(SurgeryRequest $requestModel, SurgeryChecklistItem $item, Request $req)
    {
        abort_unless($item->surgery_request_id === $requestModel->id, 404);

        $this->authorize('markChecklist', $requestModel);

        $data = $req->validate(['checked' => ['required','boolean']]);

        $item->update([
            'checked'    => $data['checked'],
            'checked_at' => $data['checked'] ? now() : null,
            'checked_by' => $data['checked'] ? Auth::id() : null,
        ]);

        return back()->with('ok', 'Checklist atualizado.');
    }

    // Aprovar (enfermeiro/admin)
    public function approve(SurgeryRequest $requestModel)
    {
        $this->authorize('approve', $requestModel);

        $hasItems = $requestModel->checklistItems()->exists();
        if ($hasItems) {
            $unchecked = $requestModel->checklistItems()->where('checked', false)->count();
            if ($unchecked > 0) {
                throw ValidationException::withMessages([
                    'checklist' => 'Existem itens do checklist ainda não marcados.'
                ]);
            }
        }

        return DB::transaction(function () use ($requestModel) {
            DB::select('SELECT id FROM surgery_requests WHERE date = ? FOR UPDATE', [$requestModel->date]);

            $overlap = SurgeryRequest::where('date', $requestModel->date)
                ->where('id', '!=', $requestModel->id)
                ->whereIn('status', ['approved'])
                ->where('start_time', '<', $requestModel->end_time)
                ->where('end_time', '>', $requestModel->start_time)
                ->exists();

            if ($overlap) {
                throw ValidationException::withMessages([
                    'approve' => 'Conflito: outro procedimento foi aprovado nesse intervalo.'
                ]);
            }

            $requestModel->update([
                'status'   => 'approved',
                'nurse_id' => Auth::id(),
            ]);

            return back()->with('ok', 'Solicitação aprovada!');
        });
    }

    // Reprovar (enfermeiro/admin)
    public function reject(SurgeryRequest $requestModel, Request $req)
    {
        $this->authorize('reject', $requestModel);

        $requestModel->update([
            'status'   => 'rejected',
            'nurse_id' => Auth::id(),
            'meta'     => array_merge($requestModel->meta ?? [], [
                'reject_reason' => (string) $req->input('reason', '')
            ]),
        ]);

        return back()->with('ok', 'Solicitação reprovada.');
    }

    // Cancelar ou remover pedido (médico/admin)
    public function destroy(SurgeryRequest $requestModel)
    {
        $user = Auth::user();

        if ($user->can('delete', $requestModel)) {
            $requestModel->delete();
            return back()->with('ok', 'Solicitação removida.');
        }

        $this->authorize('update', $requestModel);

        $requestModel->update([
            'status' => 'cancelled',
        ]);

        return back()->with('ok', 'Solicitação cancelada.');
    }
}
