<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Carbon;

class StoreSurgeryRequestRequest extends FormRequest
{
    public function authorize(): bool
    {
        // Policy vai validar depois; aqui pode liberar.
        return true;
    }

    protected function prepareForValidation(): void
    {
        if (!$this->input('end_time') && $this->input('start_time') && $this->input('duration_minutes')) {
            $end = Carbon::createFromFormat('H:i', $this->input('start_time'))
                ->addMinutes((int) $this->input('duration_minutes'))
                ->format('H:i');
            $this->merge(['end_time' => $end]);
        }
    }

    public function rules(): array
    {
        return [
            'date'         => ['required','date','after_or_equal:today'],
            'start_time'   => ['required','date_format:H:i'],
            'end_time'     => ['required','date_format:H:i','after:start_time'],
            'room_number'  => ['required','integer','between:1,9'],
            'duration_minutes' => ['required','integer','min:1'],
            'patient_name' => ['required','string','max:120'],
            'procedure'    => ['required','string','max:160'],
            // opcional: confirmar que o médico marcou "docs ok"
            'confirm_docs' => ['sometimes','boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'date.after_or_equal' => 'A data precisa ser hoje ou futura.',
            'end_time.after'      => 'O término deve ser após o início.',
        ];
    }
}
