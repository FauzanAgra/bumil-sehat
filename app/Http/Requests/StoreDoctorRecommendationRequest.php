<?php

namespace App\Http\Requests;

use App\Models\AncRecord;
use App\Models\DoctorRecommendation;
use App\Models\MotherProfile;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class StoreDoctorRecommendationRequest extends FormRequest
{
    public function authorize(): bool
    {
        $user = $this->user();

        return $user !== null && $user->isDoctor() && $user->is_verified;
    }

    public function rules(): array
    {
        return [
            'follow_up_level' => [
                'required',
                'in:'.implode(',', [
                    DoctorRecommendation::LEVEL_ROUTINE,
                    DoctorRecommendation::LEVEL_MONITOR,
                    DoctorRecommendation::LEVEL_REFER,
                ]),
            ],
            'recommendation' => ['required', 'string', 'min:5', 'max:1000'],
            'anc_record_id' => ['nullable', 'integer', 'exists:anc_records,id'],
        ];
    }

    public function messages(): array
    {
        return [
            'follow_up_level.required' => 'Pilih tingkat tindak lanjut terlebih dahulu.',
            'follow_up_level.in' => 'Tingkat tindak lanjut tidak valid.',
            'recommendation.required' => 'Catatan rekomendasi wajib diisi.',
            'recommendation.min' => 'Catatan rekomendasi minimal 5 karakter.',
            'recommendation.max' => 'Catatan rekomendasi maksimal 1000 karakter.',
            'anc_record_id.exists' => 'Catatan ANC yang dipilih tidak ditemukan.',
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator) {
            if (! $this->filled('anc_record_id')) {
                return;
            }

            /** @var MotherProfile|null $motherProfile */
            $motherProfile = $this->route('motherProfile');
            $activePregnancy = $motherProfile?->activePregnancy;

            if (! $activePregnancy) {
                $validator->errors()->add('anc_record_id', 'Pasien tidak memiliki kehamilan aktif.');

                return;
            }

            $belongsToActivePregnancy = AncRecord::where('id', $this->integer('anc_record_id'))
                ->whereHas('schedule', fn ($q) => $q->where('pregnancy_id', $activePregnancy->id))
                ->exists();

            if (! $belongsToActivePregnancy) {
                $validator->errors()->add(
                    'anc_record_id',
                    'Catatan ANC yang dipilih tidak sesuai dengan kehamilan aktif pasien.'
                );
            }
        });
    }
}
