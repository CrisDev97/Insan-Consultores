<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Advisor;
use App\Models\Service;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AdvisorController extends Controller
{
    public function index()
    {
        $advisors = Advisor::orderByDesc('id')->paginate(15);
        return view('admin.advisors.index', compact('advisors'));
    }

    public function create()
    {
        $services = Service::where('is_active', 1)->orderBy('position')->get(['id','title','sessions_count']);
        return view('admin.advisors.create', compact('services'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => ['required','string','max:255'],
            'email' => ['nullable','email','max:255'],
            'phone' => ['nullable','string','max:50'],
            'bio' => ['nullable','string'],
            'is_active' => ['nullable','boolean'],

            'services' => ['array'],
            'services.*.id' => ['integer'],
            'services.*.duration_minutes' => ['integer','min:15','max:240'],
            'services.*.is_active' => ['nullable','boolean'],
        ]);

        DB::transaction(function () use ($data) {
            $advisor = Advisor::create([
                'name' => $data['name'],
                'email' => $data['email'] ?? null,
                'phone' => $data['phone'] ?? null,
                'bio' => $data['bio'] ?? null,
                'is_active' => (bool)($data['is_active'] ?? true),
            ]);

            $pivot = [];
            foreach (($data['services'] ?? []) as $s) {
                $pivot[$s['id']] = [
                    'duration_minutes' => (int)($s['duration_minutes'] ?? 60),
                    'is_active' => (bool)($s['is_active'] ?? true),
                ];
            }
            if ($pivot) $advisor->services()->sync($pivot);
        });

        return redirect()->route('admin.advisors.index')->with('success','Asesor creado.');
    }

    public function edit(Advisor $advisor)
    {
        $services = Service::where('is_active', 1)->orderBy('position')->get(['id','title']);
        $selected = $advisor->services()->pluck('advisor_service.duration_minutes','services.id')->toArray();

        return view('admin.advisors.edit', compact('advisor','services','selected'));
    }

    public function update(Request $request, Advisor $advisor)
    {
        $data = $request->validate([
            'name' => ['required','string','max:255'],
            'email' => ['nullable','email','max:255'],
            'phone' => ['nullable','string','max:50'],
            'bio' => ['nullable','string'],
            'is_active' => ['nullable','boolean'],

            'services' => ['array'],
            'services.*.id' => ['integer'],
            'services.*.duration_minutes' => ['integer','min:15','max:240'],
            'services.*.is_active' => ['nullable','boolean'],
        ]);

        DB::transaction(function () use ($data, $advisor) {
            $advisor->update([
                'name' => $data['name'],
                'email' => $data['email'] ?? null,
                'phone' => $data['phone'] ?? null,
                'bio' => $data['bio'] ?? null,
                'is_active' => (bool)($data['is_active'] ?? true),
            ]);

            $pivot = [];
            foreach (($data['services'] ?? []) as $s) {
                $pivot[$s['id']] = [
                    'duration_minutes' => (int)($s['duration_minutes'] ?? 60),
                    'is_active' => (bool)($s['is_active'] ?? true),
                ];
            }
            $advisor->services()->sync($pivot);
        });

        return redirect()->route('admin.advisors.index')->with('success','Asesor actualizado.');
    }

    public function destroy(Advisor $advisor)
    {
        $advisor->delete();
        return back()->with('success','Asesor eliminado.');
    }
}