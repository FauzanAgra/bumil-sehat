<?php

namespace App\Http\Controllers\Mother;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

class ChatController extends Controller
{
    public function __invoke()
    {
        $user    = Auth::user();
        $midwife = $user->motherProfile?->assignedMidwife;
        $staff   = User::whereIn('role', ['midwife', 'doctor'])
            ->where('is_verified', true)
            ->with('medicalProfile.specialization')
            ->get();
        return view('dashboard.mother.chat', compact('user', 'midwife', 'staff'));
    }
}
