<?php

namespace App\Notifications;

use App\Models\RoomDowntime;
use App\Models\SurgeryRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class RoomDowntimeScheduledNotification extends Notification
{
    use Queueable;

    public function __construct(
        protected RoomDowntime $downtime,
        protected ?SurgeryRequest $surgeryRequest = null
    ) {
    }

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toDatabase(object $notifiable): array
    {
        $message = sprintf(
            'Sala %s desativada de %s até %s.',
            $this->downtime->room_number,
            $this->downtime->starts_at->format('d/m/Y H:i'),
            $this->downtime->ends_at->format('d/m/Y H:i')
        );

        if ($this->surgeryRequest) {
            $message .= sprintf(
                ' Cirurgia impactada: %s (%s às %s).',
                $this->surgeryRequest->patient_name,
                $this->surgeryRequest->date->format('d/m/Y'),
                substr($this->surgeryRequest->start_time, 0, 5)
            );
        }

        return [
            'type' => 'room_downtime.scheduled',
            'message' => $message,
            'downtime' => [
                'id' => $this->downtime->id,
                'room_number' => $this->downtime->room_number,
                'starts_at' => $this->downtime->starts_at->toIso8601String(),
                'ends_at' => $this->downtime->ends_at->toIso8601String(),
                'reason' => $this->downtime->reason,
            ],
            'surgery_request_id' => $this->surgeryRequest?->id,
        ];
    }
}
