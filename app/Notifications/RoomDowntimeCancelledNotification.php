<?php

namespace App\Notifications;

use App\Models\RoomDowntime;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class RoomDowntimeCancelledNotification extends Notification
{
    use Queueable;

    public function __construct(protected RoomDowntime $downtime)
    {
    }

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toDatabase(object $notifiable): array
    {
        return [
            'type' => 'room_downtime.cancelled',
            'message' => sprintf(
                'Sala %s reativada. Desativação de %s até %s foi cancelada.',
                $this->downtime->room_number,
                $this->downtime->starts_at->format('d/m/Y H:i'),
                $this->downtime->ends_at->format('d/m/Y H:i')
            ),
            'downtime' => [
                'id' => $this->downtime->id,
                'room_number' => $this->downtime->room_number,
                'starts_at' => $this->downtime->starts_at->toIso8601String(),
                'ends_at' => $this->downtime->ends_at->toIso8601String(),
            ],
        ];
    }
}
