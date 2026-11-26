<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Validator;

class StoreDivisionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'proker_id' => ['required', 'integer', 'exists:prokers,id'],
            'from_proker_division_id' => ['required', 'integer', 'exists:proker_divisions,id'],
            'to_proker_division_id' => ['required', 'integer', 'exists:proker_divisions,id', 'different:from_proker_division_id'],
            'title' => ['required', 'string', 'max:150'],
            'note' => ['nullable', 'string', 'max:1000'],
            'due_on' => ['required', 'date'],
        ];
    }

    public function after(): array
    {
        return [
            function (Validator $validator) {
                if ($validator->errors()->isNotEmpty()) {
                    return;
                }

                $prokerId = (int) $this->input('proker_id');
                $fromDiv = DB::table('proker_divisions')->where('id', $this->input('from_proker_division_id'))->first();
                $toDiv = DB::table('proker_divisions')->where('id', $this->input('to_proker_division_id'))->first();

                if (! $fromDiv || (int) $fromDiv->proker_id !== $prokerId) {
                    $validator->errors()->add('from_proker_division_id', 'Divisi pengirim tidak terdaftar pada proker ini.');
                }

                if (! $toDiv || (int) $toDiv->proker_id !== $prokerId) {
                    $validator->errors()->add('to_proker_division_id', 'Divisi tujuan tidak terdaftar pada proker ini.');
                }
            },
        ];
    }
}