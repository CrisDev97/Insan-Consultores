<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Advisor;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AdvisorAvailabilityController extends Controller
{
    public function edit(Advisor $advisor)
    {
        $rows = DB::table('advisor_availabilities')
            ->where('advisor_id', $advisor->id)
            ->orderBy('weekday')
            ->get();

        return view('admin.advisors.availability', compact('advisor','rows'));
    }

    public function update(Request $request, Advisor $advisor)
    {
        $data = $request->validate([
            'items' => ['array'],
            'items.*.weekday' => ['required','integer','min:0','max:6'],
            'items.*.start_time' => ['required','date_format:H:i'],
            'items.*.end_time' => ['required','date_format:H:i'],
            'items.*.slot_minutes' => ['required','integer','min:15','max:240'],
            'items.*.is_active' => ['nullable','boolean'],
        ]);

        DB::transaction(function () use ($data, $advisor) {
            DB::table('advisor_availabilities')->where('advisor_id', $advisor->id)->delete();

            foreach (($data['items'] ?? []) as $it) {
                DB::table('advisor_availabilities')->insert([
                    'advisor_id' => $advisor->id,
                    'weekday' => (int)$it['weekday'],
                    'start_time' => $it['start_time'],
                    'end_time' => $it['end_time'],
                    'slot_minutes' => (int)$it['slot_minutes'],
                    'is_active' => (bool)($it['is_active'] ?? true),
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        });

        return back()->with('success','Disponibilidad actualizada.');
    }
}