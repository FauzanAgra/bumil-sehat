<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StoreAncValidationRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        $ancRecord = $this->route('ancRecord');
        return $ancRecord->mother->assigned_midwife_id === $this->user()->id;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'notes'               => ['nullable', 'string', 'max:500'],
            'conditions'          => ['nullable', 'array'],
            'conditions.*.id'     => ['required', 'exists:detected_conditions,id'],
            'conditions.*.action' => ['required', 'in:confirm,reject'],
            'conditions.*.notes'  => ['nullable', 'string', 'max:500'],
        ];
    }
}
