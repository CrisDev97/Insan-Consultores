<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Advisor;
use App\Models\AdvisorEvent;
use Illuminate\Http\Request;

class AdvisorEventController extends Controller
{
    public function index(Request $request)
    {
        $advisorId = $request->get('advisor_id');

        $query = AdvisorEvent::with('advisor')
            ->where('is_active', 1)
            ->orderByDesc('start_at');

        if ($advisorId) {
            $query->where('advisor_id', $advisorId);
        }

        $events = $query->paginate(15);
        $advisors = Advisor::where('is_active', 1)->orderBy('name')->get(['id','name']);

        return view('admin.events.index', compact('events','advisors','advisorId'));
    }

    public function create()
    {
        $advisors = Advisor::where('is_active', 1)->orderBy('name')->get(['id','name']);
        $types = ['taller','ponencia','colegio','reunion','bloqueo','vacaciones','otro'];

        return view('admin.events.create', compact('advisors','types'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'advisor_id' => ['required','exists:advisors,id'],
            'title' => ['required','string','max:255'],
            'type' => ['required','string','max:50'],
            'start_at' => ['required','date'],
            'end_at' => ['required','date','after:start_at'],
            'location' => ['nullable','string','max:255'],
            'notes' => ['nullable','string'],
            'visibility' => ['required','in:public,private'],
            'status' => ['required','in:confirmed,tentative,cancelled'],
            'is_active' => ['nullable','boolean'],
        ]);

        AdvisorEvent::create([
            ...$data,
            'is_active' => (bool)($data['is_active'] ?? true),
        ]);

        return redirect()->route('admin.events.index')->with('success','Evento creado.');
    }

    public function edit(AdvisorEvent $event)
    {
        $advisors = Advisor::where('is_active', 1)->orderBy('name')->get(['id','name']);
        $types = ['taller','ponencia','colegio','reunion','bloqueo','vacaciones','otro'];

        return view('admin.events.edit', compact('event','advisors','types'));
    }

    public function update(Request $request, AdvisorEvent $event)
    {
        $data = $request->validate([
            'advisor_id' => ['required','exists:advisors,id'],
            'title' => ['required','string','max:255'],
            'type' => ['required','string','max:50'],
            'start_at' => ['required','date'],
            'end_at' => ['required','date','after:start_at'],
            'location' => ['nullable','string','max:255'],
            'notes' => ['nullable','string'],
            'visibility' => ['required','in:public,private'],
            'status' => ['required','in:confirmed,tentative,cancelled'],
            'is_active' => ['nullable','boolean'],
        ]);

        $event->update([
            ...$data,
            'is_active' => (bool)($data['is_active'] ?? true),
        ]);

        return redirect()->route('admin.events.index')->with('success','Evento actualizado.');
    }

    public function destroy(AdvisorEvent $event)
    {
        $event->delete();
        return back()->with('success','Evento eliminado.');
    }
}