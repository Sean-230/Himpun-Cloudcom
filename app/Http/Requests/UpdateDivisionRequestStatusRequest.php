<?php

namespace App\Http\Requests;

use App\Models\DivisionRequest;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class UpdateDivisionRequestStatusRequest extends FormRequest
{
    public function authorize(): bool
    {
        /** @var DivisionRequest $divisionRequest */
        $divisionRequest = $this->route('divisionRequest');

        return $this->user() !== null && $this->user()->can('respond', $divisionRequest);
    }

    public function rules(): array
    {
        return [
            'status' => ['required', 'string', Rule::in(DivisionRequest::STATUSES)],
        ];
    }

    public function after(): array
    {
        return [
            function (Validator $validator) {
                /** @var DivisionRequest $divisionRequest */
                $divisionRequest = $this->route('divisionRequest');
                $nextStatus = (string) $this->input('status');

                if ($divisionRequest && ! $divisionRequest->canTransitionTo($nextStatus)) {
                    $validator->errors()->add(
                        'status',
                        "Perubahan status dari '{$divisionRequest->status}' ke '{$nextStatus}' tidak diizinkan."
                    );
                }
            },
        ];
    }
}