<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreSurgeryRequestRequest;
use App\Models\SurgeryChecklistItem;
use App\Models\SurgeryRequest;
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
            return array_merge(
                $r->only([
                    'id',
                    'date',
                    'start_time',
                    'end_time',
                    'room_number',
                    'duration_minutes',
                    'patient_name',
                    'procedure',
                    'status',
                ]),
                [
                    'can_cancel' => Auth::user()->can('delete', $r) || Auth::user()->can('update', $r),
                ]
            );
        });

        return inertia('Medico/MinhasSolicitacoes', [
            'requests' => $data,
        ]);
    }

    // Lista geral (para enfermagem/admin) com filtro por status
    public function index(Request $req)
    {
        $this->authorize('viewAny', SurgeryRequest::class);

        $q = SurgeryRequest::query()->with(['doctor', 'nurse'])
            ->when($req->status, fn ($qq) => $qq->where('status', $req->status))
            ->when($req->room, fn ($qq) => $qq->where('room_number', $req->room))
            ->orderBy('date')->orderBy('start_time');

        $requests = $q->paginate(20);
        $requests->getCollection()->transform(function ($r) {
            return array_merge(
                $r->only([
                    'id',
                    'date',
                    'start_time',
                    'end_time',
                    'room_number',
                    'duration_minutes',
                    'patient_name',
                    'procedure',
                    'status',
                ]),
                [
                    'can_cancel' => Auth::user()->can('delete', $r) || Auth::user()->can('update', $r),
                ]
            );
        });

        return inertia('Enfermeiro/Solicitacoes', [
            'requests' => $requests,
            'filters' => ['status' => $req->status, 'room' => $req->room],
        ]);
    }

    // Formulário de criação (médico/admin)
    public function create()
    {
        $this->authorize('create', SurgeryRequest::class);

        return inertia('Medico/NovaSolicitacao');
    }

    // Formulário de edição
    public function edit(SurgeryRequest $surgeryRequest)
    {
        $this->authorize('update', $surgeryRequest);

        return inertia('Medico/NovaSolicitacao', [
            'request' => $surgeryRequest->only([
                'id',
                'date',
                'start_time',
                'end_time',
                'room_number',
                'duration_minutes',
                'patient_name',
                'procedure',
                'status',
                'meta',
            ]),
        ]);
    }

    // Criar pedido (médico/admin)
    public function store(StoreSurgeryRequestRequest $request)
    {
        $this->authorize('create', SurgeryRequest::class);
        $data = $request->validated();
        $date = $data['date'];
        $start = $data['start_time'];
        $end = $data['end_time'];

        return DB::transaction(function () use ($date, $start, $end, $data) {

            // 1) LOCK por dia (anti-corrida)
            if (DB::connection()->getDriverName() !== 'sqlite') {
                DB::select('SELECT id FROM surgery_requests WHERE date = ? FOR UPDATE', [$date]);
            }

            // 2) Checagem de sobreposição (global)
            $conflict = SurgeryRequest::where('date', $date)
                ->whereIn('status', ['requested', 'approved'])
                ->where('start_time', '<', $end)
                ->where('end_time', '>', $start)
                ->first();

            if ($conflict) {
                $msg = sprintf(
                    'Conflito de horário com cirurgia na sala %s de %s às %s.',
                    $conflict->room_number,
                    substr($conflict->start_time, 0, 5),
                    substr($conflict->end_time, 0, 5)
                );
                if ($conflict->patient_name) {
                    $msg .= ' Paciente: '.$conflict->patient_name.'.';
                }
                throw ValidationException::withMessages([
                    'start_time' => $msg,
                ]);
            }

            // 3) Cria a solicitação
            SurgeryRequest::create([
                'doctor_id' => Auth::id(),
                'date' => $date,
                'start_time' => $start,
                'end_time' => $end,
                'room_number' => $data['room_number'],
                'duration_minutes' => $data['duration_minutes'],
                'patient_name' => $data['patient_name'],
                'procedure' => $data['procedure'],
                'status' => 'requested',
                'meta' => ['confirm_docs' => (bool) ($data['confirm_docs'] ?? false)],
            ]);

            return back()->with('ok', 'Solicitação criada!');
        });
    }

    // Atualizar pedido
    public function update(StoreSurgeryRequestRequest $req, SurgeryRequest $surgeryRequest)
    {
        $data = $req->validated();

        $this->authorize('update', [$surgeryRequest, [
            'room_number' => $data['room_number'],
            'duration_minutes' => $data['duration_minutes'],
        ]]);

        $date = $data['date'];
        $start = $data['start_time'];
        $end = $data['end_time'];

        return DB::transaction(function () use ($date, $start, $end, $data, $surgeryRequest) {

            // LOCK por dia para evitar corrida
            if (DB::connection()->getDriverName() !== 'sqlite') {
                DB::select('SELECT id FROM surgery_requests WHERE date = ? FOR UPDATE', [$date]);
            }

            $conflict = SurgeryRequest::where('date', $date)
                ->where('id', '!=', $surgeryRequest->id)
                ->whereIn('status', ['requested', 'approved'])
                ->where('start_time', '<', $end)
                ->where('end_time', '>', $start)
                ->first();

            if ($conflict) {
                $msg = sprintf(
                    'Conflito de horário com cirurgia na sala %s de %s às %s.',
                    $conflict->room_number,
                    substr($conflict->start_time, 0, 5),
                    substr($conflict->end_time, 0, 5)
                );
                if ($conflict->patient_name) {
                    $msg .= ' Paciente: '.$conflict->patient_name.'.';
                }
                throw ValidationException::withMessages([
                    'start_time' => $msg,
                ]);
            }

            $surgeryRequest->update([
                'date' => $date,
                'start_time' => $start,
                'end_time' => $end,
                'room_number' => $data['room_number'],
                'duration_minutes' => $data['duration_minutes'],
                'patient_name' => $data['patient_name'],
                'procedure' => $data['procedure'],
                'meta' => array_merge($surgeryRequest->meta ?? [], [
                    'confirm_docs' => (bool) ($data['confirm_docs'] ?? false),
                ]),
            ]);

            return back()->with('ok', 'Solicitação atualizada!');
        });
    }

    // Atualizar item do checklist (enfermeiro/admin)
    public function updateChecklistItem(SurgeryRequest $surgeryRequest, SurgeryChecklistItem $item, Request $req)
    {
        abort_unless($item->surgery_request_id === $surgeryRequest->id, 404);

        $this->authorize('markChecklist', $surgeryRequest);

        $data = $req->validate(['checked' => ['required', 'boolean']]);

        $item->update([
            'checked' => $data['checked'],
            'checked_at' => $data['checked'] ? now() : null,
            'checked_by' => $data['checked'] ? Auth::id() : null,
        ]);

        return back()->with('ok', 'Checklist atualizado.');
    }

    // Aprovar (enfermeiro/admin)
    public function approve(SurgeryRequest $surgeryRequest)
    {
        $this->authorize('approve', $surgeryRequest);

        $hasItems = $surgeryRequest->checklistItems()->exists();
        if ($hasItems) {
            $unchecked = $requestModel->checklistItems()->where('checked', false)->count();
            if ($unchecked > 0) {
                throw ValidationException::withMessages([
                    'checklist' => 'Existem itens do checklist ainda não marcados.',
                ]);
            }
        }

        return DB::transaction(function () use ($surgeryRequest) {
            if (DB::connection()->getDriverName() !== 'sqlite') {
                DB::select('SELECT id FROM surgery_requests WHERE date = ? FOR UPDATE', [$surgeryRequest->date]);
            }

            $conflict = SurgeryRequest::where('date', $surgeryRequest->date)
                ->where('id', '!=', $surgeryRequest->id)
                ->whereIn('status', ['approved'])
                ->where('start_time', '<', $surgeryRequest->end_time)
                ->where('end_time', '>', $surgeryRequest->start_time)
                ->first();

            if ($conflict) {
                $msg = sprintf(
                    'Conflito: sala %s ocupada de %s às %s.',
                    $conflict->room_number,
                    substr($conflict->start_time, 0, 5),
                    substr($conflict->end_time, 0, 5)
                );
                if ($conflict->patient_name) {
                    $msg .= ' Paciente: '.$conflict->patient_name.'.';
                }
                throw ValidationException::withMessages([
                    'approve' => $msg,
                ]);
            }

            $surgeryRequest->update([
                'status' => 'approved',
                'nurse_id' => Auth::id(),
            ]);

            return back()->with('ok', 'Solicitação aprovada!');
        });
    }

    // Reprovar (enfermeiro/admin)
    public function reject(SurgeryRequest $surgeryRequest, Request $req)
    {
        $this->authorize('reject', $surgeryRequest);

        $surgeryRequest->update([
            'status' => 'rejected',
            'nurse_id' => Auth::id(),
            'meta' => array_merge($surgeryRequest->meta ?? [], [
                'reject_reason' => (string) $req->input('reason', ''),
            ]),
        ]);

        return back()->with('ok', 'Solicitação reprovada.');
    }

    // Cancelar ou remover pedido (médico/admin)
    public function destroy(SurgeryRequest $surgeryRequest)
    {
        $user = Auth::user();

        if ($user->can('delete', $surgeryRequest)) {
            $surgeryRequest->delete();

            return back()->with('ok', 'Solicitação removida.');
        }

        $this->authorize('update', $surgeryRequest);

        $surgeryRequest->update([
            'status' => 'cancelled',
        ]);

        return back()->with('ok', 'Solicitação cancelada.');
    }
}
