<?php

namespace App\Http\Requests;

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
            'conditions.*.id'     => ['required', 'integer', 'exists:detected_conditions,id'],
            'conditions.*.action' => ['required', 'string', 'in:confirm,reject'],
            'conditions.*.notes'  => ['nullable', 'string', 'max:500'],
        ];
    }

    public function messages(): array
    {
        return [
            'notes.max'                   => 'Catatan bidan maksimal 500 karakter.',
            'conditions.*.id.exists'      => 'Kondisi tidak ditemukan.',
            'conditions.*.action.in'      => 'Aksi kondisi harus "confirm" atau "reject".',
            'conditions.*.notes.max'      => 'Catatan kondisi maksimal 500 karakter.',
        ];
    }

    public function attributes(): array
    {
        return [
            'notes'               => 'catatan bidan',
            'conditions'          => 'kondisi',
            'conditions.*.id'     => 'ID kondisi',
            'conditions.*.action' => 'aksi kondisi',
            'conditions.*.notes'  => 'catatan kondisi',
        ];
    }
}
