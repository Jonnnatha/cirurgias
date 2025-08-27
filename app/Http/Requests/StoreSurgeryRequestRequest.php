<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreSurgeryRequestRequest extends FormRequest
{
    public function authorize(): bool
    {
        // Policy vai validar depois; aqui pode liberar.
        return true;
    }

    public function rules(): array
    {
        return [
            'date'         => ['required','date','after_or_equal:today'],
            'start_time'   => ['required','date_format:H:i'],
            'end_time'     => ['required','date_format:H:i','after:start_time'],
            'room'         => ['required','string','max:50'],
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
