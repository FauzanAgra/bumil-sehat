<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Area;
use App\Models\MidwifeArea;
use App\Models\MotherProfile;
use App\Models\Specialization;
use App\Models\Subdistrict;
use App\Models\User;
use App\Services\NIKService;
use Carbon\Carbon;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class RegisterController extends Controller
{
    // ── Step 0: Pilih role ────────────────────────────────────────────
    public function chooseRole()
    {
        return view('auth.choose-role');
    }

    // ── Step 1: Form akun ibu hamil ───────────────────────────────────
    public function showMother()
    {
        return view('auth.register-mother');
    }

    public function storeMother(Request $request)
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'phone' => ['required', 'string', 'unique:users,phone'],
            'password' => ['required', 'confirmed', Password::min(6)],
        ], [
            'phone.unique' => 'Nomor HP sudah terdaftar.',
        ]);

        // Simpan ke session, belum ke database
        session([
            'reg_mother.name' => $request->name,
            'reg_mother.phone' => $request->phone,
            'reg_mother.password' => Hash::make($request->password),
        ]);

        return redirect()->route('register.mother.identity');
    }

    // ── Step 2: Verifikasi identitas ──────────────────────────────────
    public function showIdentity()
    {
        // Pastikan step 1 sudah diisi
        if (! session('reg_mother.name')) {
            return redirect()->route('register.mother');
        }

        $subdistricts = Subdistrict::orderBy('name')->get();
        $areas = Area::with('subdistrict')->orderBy('rw_number')->get();

        return view('auth.verify-identity', compact('subdistricts', 'areas'));
    }

    public function storeIdentity(Request $request)
    {
        $request->validate([
            'nik' => ['required', 'digits:16'],
            'birth_date' => ['required', 'date', 'before:today'],
            'address' => ['required', 'string'],
            'subdistrict_id' => ['required', 'exists:subdistricts,id'],
            'area_id' => ['required', 'exists:areas,id'],
            'rt' => ['nullable', 'string', 'max:10'],
        ], [
            'nik.digits' => 'NIK harus 16 digit angka.',
            'birth_date.before' => 'Tanggal lahir tidak valid.',
        ]);

        // Cek NIK sudah terdaftar
        $nikHash = app(NIKService::class)->hashNIK($request->nik);
        if (MotherProfile::where('nik_hash', $nikHash)->exists()) {
            return back()->withInput()
                ->withErrors(['nik' => 'NIK sudah terdaftar.']);
        }

        // Simpan ke session
        session([
            'reg_mother.nik' => $request->nik,
            'reg_mother.birth_date' => $request->birth_date,
            'reg_mother.address' => $request->address,
            'reg_mother.subdistrict_id' => $request->subdistrict_id,
            'reg_mother.area_id' => $request->area_id,
            'reg_mother.rt' => $request->rt,
        ]);

        return redirect()->route('register.mother.hpht');
    }

    // ── Step 3: Input HPHT ────────────────────────────────────────────
    public function showHpht()
    {
        // Pastikan step 2 sudah diisi
        if (! session('reg_mother.nik')) {
            return redirect()->route('register.mother');
        }

        // Cari bidan pendamping berdasarkan area
        $areaId = session('reg_mother.area_id');
        $midwife = null;

        if ($areaId) {
            $midwife = MidwifeArea::with('midwife')
                ->where('area_id', $areaId)
                ->where('is_active', true)
                ->first()?->midwife;
        }

        return view('auth.input-hpht', compact('midwife'));
    }

    public function storeHpht(Request $request)
    {
        $request->validate([
            'hpht_date' => ['required', 'date', 'before:today'],
        ], [
            'hpht_date.before' => 'Tanggal HPHT tidak valid.',
        ]);

        $data = session('reg_mother');

        if (! $data) {
            return redirect()->route('register.mother');
        }

        $nikService = app(NIKService::class);
        $nikHash = $nikService->hashNIK($data['nik']);

        // Re-check di step akhir sebelum commit ke DB
        if (MotherProfile::where('nik_hash', $nikHash)->exists()) {
            session()->forget('reg_mother');

            return redirect()->route('register.mother')
                ->withErrors(['nik' => 'NIK sudah terdaftar. Silakan ulangi registrasi.']);
        }

        // Hitung HPL: HPHT + 280 hari (Naegele rule)
        $hpht = Carbon::parse($request->hpht_date);
        $hpl = $hpht->copy()->addDays(280);

        // Cari bidan pendamping
        $midwifeArea = MidwifeArea::where('area_id', $data['area_id'])
            ->where('is_active', true)
            ->first();

        try {
            // Buat user
            $user = User::create([
                'name' => $data['name'],
                'phone' => $data['phone'],
                'password' => $data['password'],
                'role' => 'mother',
                'is_verified' => true,
            ]);

            // Buat mother profile
            $user->motherProfile()->create([
                'nik_encrypted' => $nikService->encrypt($data['nik']),
                'nik_hash' => $nikService->hashNIK($data['nik']),
                'birth_date' => $data['birth_date'],
                'address' => $data['address'],
                'subdistrict_id' => $data['subdistrict_id'],
                'area_id' => $data['area_id'],
                'rt' => $data['rt'],
                'assigned_midwife_id' => $midwifeArea?->midwife_id,
            ]);

            // Buat pregnancy
            $motherProfile = $user->motherProfile;
            $motherProfile->pregnancies()->create([
                'hpht_date' => $hpht,
                'hpl_date' => $hpl,
                'status' => 'active',
            ]);
        } catch (QueryException $e) {
            if ($e->errorInfo[1] === 1062) { // MySQL duplicate entry
                session()->forget('reg_mother');

                return redirect()->route('register.mother')
                    ->withErrors(['nik' => 'NIK sudah terdaftar. Silakan ulangi registrasi.']);
            }
            throw $e;
        }

        // Bersihkan session registrasi
        session()->forget('reg_mother');

        Auth::login($user);

        return redirect()->route('mother.dashboard');
    }

    // ── Registrasi tenaga medis ───────────────────────────────────────
    public function showMedical()
    {
        $specializations = Specialization::active()->get();

        return view('auth.register-medical', compact('specializations'));
    }

    public function storeMedical(Request $request)
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'phone' => ['required', 'string', 'unique:users,phone'],
            'password' => ['required', 'confirmed', Password::min(6)],
            'specialization_id' => ['required', 'exists:specializations,id'],
            'license_number' => ['required', 'string'],
            'degree_prefix' => ['nullable', 'string', 'max:20'],
            'degree_suffix' => ['nullable', 'string', 'max:50'],
            'availability' => ['nullable', 'string'],
        ], [
            'phone.unique' => 'Nomor HP sudah terdaftar.',
        ]);

        $specialization = Specialization::findOrFail($request->specialization_id);
        $role = $specialization->role_group === 'midwife' ? 'midwife' : 'doctor';

        $user = User::create([
            'name' => $request->name,
            'phone' => $request->phone,
            'password' => Hash::make($request->password),
            'role' => $role,
            'is_verified' => false,
        ]);

        $user->medicalProfile()->create([
            'specialization_id' => $request->specialization_id,
            'license_number' => $request->license_number,
            'degree_prefix' => $request->degree_prefix,
            'degree_suffix' => $request->degree_suffix,
            'availability' => $request->availability,
        ]);

        Auth::login($user);

        return redirect()->route('waiting-approval');
    }
}
