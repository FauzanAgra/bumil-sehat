<?php

namespace App\Http\Controllers\Midwife;

use App\Http\Controllers\Controller;

class ProfileController extends Controller
{
    public function show()
    {
        $user = auth()->user()->load([
            'medicalProfile.specialization',
            'midwifeAreas.area.subdistrict',
        ]);

        return view('dashboard.midwife.setting', compact('user'));
    }
}
