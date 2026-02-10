<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ContactMessage;
use Illuminate\Http\Request;

class ContactMessageController extends Controller
{
  public function index(Request $request)
  {
    $q = ContactMessage::query();

    if ($request->filled('search')) {
        $s = trim($request->get('search'));
        $q->where(function ($sub) use ($s) {
        $sub->where('name', 'like', "%{$s}%")
            ->orWhere('email', 'like', "%{$s}%");
        });
    }

    // filtros: read (0/1), contacted (0/1)
    if ($request->filled('read')) {
      $read = $request->boolean('read');
      $read ? $q->whereNotNull('read_at') : $q->whereNull('read_at');
    }

    if ($request->filled('contacted')) {
      $contacted = $request->boolean('contacted');
      $contacted ? $q->whereNotNull('contacted_at') : $q->whereNull('contacted_at');
    }

    $messages = $q
      ->orderByRaw('read_at is null desc')   // no leídos primero
      ->orderByDesc('created_at')
      ->paginate(12)
      ->withQueryString();

    $stats = [
      'total' => ContactMessage::count(),
      'unread' => ContactMessage::whereNull('read_at')->count(),
      'not_contacted' => ContactMessage::whereNull('contacted_at')->count(),
    ];

    return view('admin.messages.index', compact('messages','stats'));
  }

  public function markAllRead()
    {
    \App\Models\ContactMessage::whereNull('read_at')->update(['read_at' => now()]);
    return back()->with('success', 'Todos los mensajes fueron marcados como leídos.');
    }

  public function show(ContactMessage $message)
  {
    // al abrir, marcar como leído si no lo está
    if (is_null($message->read_at)) {
      $message->update(['read_at' => now()]);
    }

    return view('admin.messages.show', compact('message'));
  }

  public function toggleRead(ContactMessage $message)
  {
    $message->update([
      'read_at' => is_null($message->read_at) ? now() : null,
    ]);

    return back()->with('success', is_null($message->read_at) ? 'Marcado como no leído.' : 'Marcado como leído.');
  }

  public function toggleContacted(ContactMessage $message)
  {
    $message->update([
      'contacted_at' => is_null($message->contacted_at) ? now() : null,
    ]);

    return back()->with('success', is_null($message->contacted_at) ? 'Marcado como no contactado.' : 'Marcado como contactado.');
  }

  public function destroy(ContactMessage $message)
  {
    $message->delete();
    return redirect()->route('admin.messages.index')->with('success', 'Mensaje eliminado.');
  }
}
