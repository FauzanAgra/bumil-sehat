<?php

namespace App\Http\Controllers\Mother;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;

class TtdController extends Controller
{
    public function __invoke()
    {
        $ttd = Auth::user()->motherProfile?->activePregnancy?->ttdStatus;
        if ($ttd?->is_active) {
            $ttd->logs()->updateOrCreate(
                ['date' => today()],
                ['is_taken' => true]
            );
        }

        return back();
    }
}
