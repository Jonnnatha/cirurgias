<?php

namespace App\Http\Controllers;

use App\Models\RoomDowntime;
use App\Models\SurgeryRequest;
use App\Models\SurgeryRescheduleRequest;
use App\Models\User;
use App\Notifications\RoomDowntimeCancelledNotification;
use App\Notifications\RoomDowntimeScheduledNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;

class RoomDowntimeController extends Controller
{
    public function store(Request $request)
    {
        abort_unless($request->user()->hasRole('admin'), 403);

        $data = $request->validate([
            'room_number' => ['required', 'integer', 'min:1', 'max:8'],
            'starts_at' => ['required', 'date'],
            'ends_at' => ['required', 'date', 'after:starts_at'],
            'reason' => ['nullable', 'string', 'max:255'],
        ]);

        $startsAt = Carbon::parse($data['starts_at']);
        $endsAt = Carbon::parse($data['ends_at']);

        [$downtime, $impactedSurgeries] = DB::transaction(function () use ($data, $startsAt, $endsAt, $request) {
            $downtime = RoomDowntime::create([
                'room_number' => $data['room_number'],
                'starts_at' => $startsAt,
                'ends_at' => $endsAt,
                'reason' => $data['reason'] ?? null,
                'created_by' => $request->user()->id,
            ]);

            $reason = 'Sala desativada'.($downtime->reason ? ': '.$downtime->reason : '.');

            $surgeries = SurgeryRequest::query()
                ->with(['doctor', 'nurse'])
                ->where('room_number', $downtime->room_number)
                ->whereIn('status', ['requested', 'approved'])
                ->whereDate('date', '>=', $startsAt->toDateString())
                ->whereDate('date', '<=', $endsAt->toDateString())
                ->get()
                ->filter(function (SurgeryRequest $surgery) use ($startsAt, $endsAt) {
                    $surgeryStart = Carbon::parse($surgery->date->format('Y-m-d').' '.$surgery->start_time);
                    $surgeryEnd = Carbon::parse($surgery->date->format('Y-m-d').' '.$surgery->end_time);

                    return $surgeryStart < $endsAt && $surgeryEnd > $startsAt;
                });

            $surgeries->each(function (SurgeryRequest $surgery) use ($downtime, $reason) {
                $exists = $surgery->rescheduleRequests()
                    ->where('room_downtime_id', $downtime->id)
                    ->where('status', 'pending')
                    ->exists();

                if (!$exists) {
                    $surgery->rescheduleRequests()->create([
                        'room_downtime_id' => $downtime->id,
                        'status' => 'pending',
                        'reason' => $reason,
                    ]);
                }
            });

            return [$downtime, $surgeries];
        });

        // Envia notificações para cirurgias impactadas
        $impactedSurgeries->each(function (SurgeryRequest $surgery) use ($downtime) {
            if ($surgery->doctor) {
                $surgery->doctor->notify(new RoomDowntimeScheduledNotification($downtime, $surgery));
            }
            if ($surgery->nurse) {
                $surgery->nurse->notify(new RoomDowntimeScheduledNotification($downtime, $surgery));
            }
        });

        // Notifica administradores e enfermagem sobre a nova desativação
        $watchers = User::role(['admin', 'enfermeiro'])->get();
        if ($watchers->isNotEmpty()) {
            Notification::send($watchers, new RoomDowntimeScheduledNotification($downtime));
        }

        return response()->json([
            'downtime' => $this->formatDowntime($downtime),
            'impacted_surgeries' => $impactedSurgeries->map(fn ($surgery) => $this->formatSurgery($surgery)),
        ], 201);
    }

    public function indexBanners(Request $request)
    {
        abort_unless($request->user()->hasAnyRole(['medico', 'enfermeiro', 'admin']), 403);

        $data = $request->validate([
            'start_date' => ['nullable', 'date'],
            'end_date' => ['nullable', 'date', 'after_or_equal:start_date'],
        ]);

        $query = RoomDowntime::query()->whereNull('cancelled_at');

        if ($request->filled('start_date') && $request->filled('end_date')) {
            $start = Carbon::parse($data['start_date'])->startOfDay();
            $end = Carbon::parse($data['end_date'])->endOfDay();

            $query->where(function ($q) use ($start, $end) {
                $q->whereBetween('starts_at', [$start, $end])
                    ->orWhereBetween('ends_at', [$start, $end])
                    ->orWhere(function ($qq) use ($start, $end) {
                        $qq->where('starts_at', '<=', $start)
                            ->where('ends_at', '>=', $end);
                    });
            });
        } else {
            $todayStart = Carbon::now()->startOfDay();
            $todayEnd = Carbon::now()->endOfDay();

            $query->where('ends_at', '>=', $todayStart)
                ->where('starts_at', '<=', $todayEnd);
        }

        $downtimes = $query->orderBy('starts_at')->get()->map(fn ($downtime) => $this->formatDowntime($downtime));

        return response()->json(['data' => $downtimes]);
    }

    public function byRoom(Request $request, int $roomNumber)
    {
        abort_unless($request->user()->hasAnyRole(['medico', 'enfermeiro', 'admin']), 403);

        $downtimes = RoomDowntime::query()
            ->where('room_number', $roomNumber)
            ->whereNull('cancelled_at')
            ->where('ends_at', '>=', Carbon::now())
            ->orderBy('starts_at')
            ->get()
            ->map(fn ($downtime) => $this->formatDowntime($downtime));

        return response()->json(['data' => $downtimes]);
    }

    public function cancel(Request $request, RoomDowntime $roomDowntime)
    {
        abort_unless($request->user()->hasRole('admin'), 403);

        if ($roomDowntime->cancelled_at) {
            return response()->json([
                'downtime' => $this->formatDowntime($roomDowntime),
                'message' => 'Desativação já cancelada.',
            ]);
        }

        $pendingReschedules = collect();

        DB::transaction(function () use ($roomDowntime, $request, &$pendingReschedules) {
            $roomDowntime->update([
                'cancelled_at' => Carbon::now(),
                'cancelled_by' => $request->user()->id,
            ]);

            $pendingReschedules = $roomDowntime->rescheduleRequests()
                ->where('status', 'pending')
                ->with('surgeryRequest.doctor', 'surgeryRequest.nurse')
                ->get();

            $roomDowntime->rescheduleRequests()
                ->where('status', 'pending')
                ->update([
                    'status' => 'cancelled',
                    'resolved_at' => Carbon::now(),
                ]);
        });

        $pendingReschedules->each(function (SurgeryRescheduleRequest $reschedule) use ($roomDowntime) {
            $surgery = $reschedule->surgeryRequest;
            if ($surgery?->doctor) {
                $surgery->doctor->notify(new RoomDowntimeCancelledNotification($roomDowntime));
            }
            if ($surgery?->nurse) {
                $surgery->nurse->notify(new RoomDowntimeCancelledNotification($roomDowntime));
            }
        });

        $watchers = User::role(['admin', 'enfermeiro'])->get();
        if ($watchers->isNotEmpty()) {
            Notification::send($watchers, new RoomDowntimeCancelledNotification($roomDowntime));
        }

        return response()->json([
            'downtime' => $this->formatDowntime($roomDowntime->fresh()),
            'message' => 'Desativação cancelada.',
        ]);
    }

    public function alerts(Request $request)
    {
        abort_unless($request->user()->hasAnyRole(['medico', 'enfermeiro', 'admin']), 403);

        $user = $request->user();
        $now = Carbon::now();
        $limit = $now->copy()->addDays(30);

        $downtimes = RoomDowntime::query()
            ->whereNull('cancelled_at')
            ->where('starts_at', '<=', $limit)
            ->where('ends_at', '>=', $now)
            ->orderBy('starts_at')
            ->get()
            ->map(fn ($downtime) => array_merge($this->formatDowntime($downtime), [
                'active' => $downtime->isActive(),
            ]));

        $reschedulesQuery = SurgeryRescheduleRequest::query()
            ->with(['surgeryRequest', 'roomDowntime'])
            ->where('status', 'pending');

        $isDoctor = $user->hasRole('medico');
        $canSeeAll = $user->hasAnyRole(['enfermeiro', 'admin']);

        if ($isDoctor && !$canSeeAll) {
            $reschedulesQuery->whereHas('surgeryRequest', fn ($q) => $q->where('doctor_id', $user->id));
        }

        $reschedules = $reschedulesQuery->get()->filter(function (SurgeryRescheduleRequest $reschedule) use ($limit, $now) {
            $downtime = $reschedule->roomDowntime;
            if (!$downtime || $downtime->cancelled_at) {
                return false;
            }
            return $downtime->starts_at <= $limit && $downtime->ends_at >= $now;
        });

        $impacted = $reschedules->map(function (SurgeryRescheduleRequest $reschedule) {
            $surgery = $reschedule->surgeryRequest;
            $downtime = $reschedule->roomDowntime;

            return [
                'id' => $reschedule->id,
                'surgery_request_id' => $surgery?->id,
                'patient_name' => $surgery?->patient_name,
                'date' => $surgery?->date?->toDateString(),
                'start_time' => $surgery?->start_time,
                'room_number' => $surgery?->room_number,
                'reason' => $reschedule->reason,
                'downtime' => $downtime ? $this->formatDowntime($downtime) : null,
            ];
        })->values();

        return response()->json([
            'downtimes' => $downtimes->values(),
            'impacted_surgeries' => $impacted,
        ]);
    }

    protected function formatDowntime(RoomDowntime $downtime): array
    {
        return [
            'id' => $downtime->id,
            'room_number' => $downtime->room_number,
            'starts_at' => $downtime->starts_at?->toIso8601String(),
            'ends_at' => $downtime->ends_at?->toIso8601String(),
            'reason' => $downtime->reason,
            'cancelled_at' => $downtime->cancelled_at?->toIso8601String(),
        ];
    }

    protected function formatSurgery(SurgeryRequest $surgery): array
    {
        return [
            'id' => $surgery->id,
            'patient_name' => $surgery->patient_name,
            'date' => $surgery->date?->toDateString(),
            'start_time' => $surgery->start_time,
            'end_time' => $surgery->end_time,
            'room_number' => $surgery->room_number,
            'status' => $surgery->status,
        ];
    }
}
