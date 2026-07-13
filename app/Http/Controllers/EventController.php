<?php

namespace App\Http\Controllers;

use App\Exports\EventsExport;
use App\Http\Requests\EventFormRequest;
use App\Models\Event;
use App\Models\Kategori;
use App\Models\Tiket;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Facades\Excel;

class EventController extends Controller
{
    /**
     * Display a listing of the events (admin).
     */
    public function index(Request $request)
    {
        $eventsQuery = Event::with(['kategori', 'tikets']);

        if ($request->filled('kategori_id')) {
            $eventsQuery->where('kategori_id', $request->kategori_id);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $eventsQuery->where(function ($query) use ($search) {
                $query->where('judul', 'like', "%{$search}%")
                    ->orWhere('lokasi', 'like', "%{$search}%");
            });
        }

        $sort = $request->get('sort', 'asc');
        $eventsQuery->orderBy('tanggal_waktu', $sort);

        $events = $eventsQuery->paginate(10);
        $categories = Kategori::all();

        foreach ($events as $event) {
            $event->recordStatusChangeIfNeeded();
        }

        return view('pages.admin.events.index', [
            'events' => $events,
            'categories' => $categories,
        ]);
    }

    /**
     * Show the form for creating a new event.
     */
    public function create()
    {
        $categories = Kategori::all();

        return view('pages.admin.events.create', [
            'categories' => $categories,
        ]);
    }

    /**
     * Store a newly created event in storage.
     */
    public function store(EventFormRequest $request)
    {
        $validated = $request->validated();

        if ($request->hasFile('gambar')) {
            $gambar = $request->file('gambar')->store('events', 'public');
        } else {
            $gambar = 'konser.jpg';
        }

        $event = Event::create([
            'user_id' => auth()->id(),
            'kategori_id' => $validated['kategori_id'],
            'judul' => $validated['judul'],
            'deskripsi' => $validated['deskripsi'],
            'lokasi' => $validated['lokasi'],
            'gambar' => $gambar,
            'tanggal_waktu' => $validated['tanggal_waktu'],
        ]);

        foreach ($validated['tikets'] as $tiket) {
            $event->tikets()->create([
                'tipe' => $tiket['tipe'],
                'harga' => $tiket['harga'],
                'stok' => $tiket['stok'],
            ]);
        }

        $this->recordHistory($event, 'created', 'Event dibuat.');

        return redirect()->route('admin.events.index')
            ->with('success', 'Event berhasil ditambahkan!');
    }

    /**
     * Show the form for editing the specified event.
     */
    public function edit(Event $event)
    {
        $event->recordStatusChangeIfNeeded();
        $event->load(['tikets', 'histories.user']);
        $categories = Kategori::all();
        $hasSales = $event->hasSales();

        return view('pages.admin.events.edit', [
            'event' => $event,
            'categories' => $categories,
            'hasSales' => $hasSales,
        ]);
    }

    /**
     * Update the specified event in storage.
     */
    public function update(EventFormRequest $request, Event $event)
    {
        $validated = $request->validated();
        $hasSales = $event->hasSales();

        if ($hasSales && $validated['tanggal_waktu'] !== $event->tanggal_waktu->format('Y-m-d\TH:i')) {
            return back()->withErrors([
                'tanggal_waktu' => 'Tanggal & waktu tidak dapat diubah karena event sudah memiliki penjualan tiket.',
            ])->withInput();
        }

        if ($request->hasFile('gambar')) {
            if ($event->gambar && $event->gambar !== 'konser.jpg' && Storage::disk('public')->exists($event->gambar)) {
                Storage::disk('public')->delete($event->gambar);
            }
            $gambar = $request->file('gambar')->store('events', 'public');
        } else {
            $gambar = $event->gambar;
        }

        $event->update([
            'kategori_id' => $validated['kategori_id'],
            'judul' => $validated['judul'],
            'deskripsi' => $validated['deskripsi'],
            'lokasi' => $validated['lokasi'],
            'gambar' => $gambar,
            'tanggal_waktu' => $validated['tanggal_waktu'],
        ]);

        $submittedTiketIds = [];

        foreach ($validated['tikets'] as $tiketData) {
            if (!empty($tiketData['id'])) {
                $tiket = Tiket::find($tiketData['id']);
                if ($tiket && $tiket->event_id === $event->id) {
                    $tiket->update([
                        'tipe' => $tiketData['tipe'],
                        'harga' => $tiketData['harga'],
                        'stok' => $tiketData['stok'],
                    ]);
                    $submittedTiketIds[] = $tiket->id;
                }
            } else {
                $newTiket = $event->tikets()->create([
                    'tipe' => $tiketData['tipe'],
                    'harga' => $tiketData['harga'],
                    'stok' => $tiketData['stok'],
                ]);
                $submittedTiketIds[] = $newTiket->id;
            }
        }

        $tiketsToDelete = $event->tikets()->whereNotIn('id', $submittedTiketIds)->get();

        foreach ($tiketsToDelete as $tiket) {
            if (!$tiket->orders()->exists()) {
                $tiket->delete();
            }
        }

        $this->recordHistory($event, 'updated', 'Event diperbarui.');

        return redirect()->route('admin.events.index')
            ->with('success', 'Event berhasil diperbarui!');
    }

    /**
     * Remove the specified event from storage.
     */
    public function destroy(Event $event)
    {
        if ($event->hasSales()) {
            return back()->with('error', 'Event tidak dapat dihapus karena sudah memiliki penjualan tiket.');
        }

        if ($event->gambar && $event->gambar !== 'konser.jpg' && Storage::disk('public')->exists($event->gambar)) {
            Storage::disk('public')->delete($event->gambar);
        }

        $event->delete();

        return redirect()->route('admin.events.index')
            ->with('success', 'Event berhasil dihapus!');
    }

    /**
     * Remove multiple events from storage at once.
     */
    public function bulkDestroy(Request $request)
    {
        $request->validate([
            'event_ids' => 'required|array|min:1',
            'event_ids.*' => 'exists:events,id',
        ]);

        $events = Event::whereIn('id', $request->event_ids)->get();

        $deleted = 0;
        $skipped = 0;

        foreach ($events as $event) {
            if ($event->hasSales()) {
                $skipped++;
                continue;
            }

            if ($event->gambar && $event->gambar !== 'konser.jpg' && Storage::disk('public')->exists($event->gambar)) {
                Storage::disk('public')->delete($event->gambar);
            }

            $event->delete();
            $deleted++;
        }

        $message = "{$deleted} event berhasil dihapus.";
        if ($skipped > 0) {
            $message .= " {$skipped} event dilewati karena sudah memiliki penjualan tiket.";
        }

        return redirect()->route('admin.events.index')->with('success', $message);
    }

    /**
     * Duplicate the specified event along with its tickets.
     */
    public function clone(Event $event)
    {
        $event->load('tikets');

        $newEvent = $event->replicate();
        $newEvent->judul = $event->judul . ' (Copy)';
        $newEvent->push();

        foreach ($event->tikets as $tiket) {
            $newEvent->tikets()->create([
                'tipe' => $tiket->tipe,
                'harga' => $tiket->harga,
                'stok' => $tiket->stok,
            ]);
        }

        $this->recordHistory($newEvent, 'cloned', "Event diduplikasi dari \"{$event->judul}\" (ID {$event->id}).");

        return redirect()->route('admin.events.index')
            ->with('success', 'Event berhasil diduplikasi!');
    }

    /**
     * Export events to Excel.
     */
    public function export(Request $request)
    {
        $filters = $request->only(['kategori_id', 'search', 'sort']);

        return Excel::download(new EventsExport($filters), 'events-' . now()->format('Y-m-d_His') . '.xlsx');
    }

    /**
     * Display the specified event.
     */
    public function show(Event $event)
    {
        $event->load(['kategori', 'tikets']);

        $relatedEvents = Event::with(['kategori', 'tikets'])
            ->where('kategori_id', $event->kategori_id)
            ->where('id', '!=', $event->id)
            ->where('tanggal_waktu', '>', now())
            ->limit(4)
            ->get()
            ->map(function ($relatedEvent) {
                $relatedEvent->tikets_min_harga = $relatedEvent->tikets->min('harga') ?? 0;
                return $relatedEvent;
            });

        return view('events.show', [
            'event' => $event,
            'relatedEvents' => $relatedEvents,
        ]);
    }

    /**
     * Record a history entry for the given event.
     */
    protected function recordHistory(Event $event, string $action, ?string $keterangan = null): void
    {
        $event->histories()->create([
            'user_id' => auth()->id(),
            'action' => $action,
            'status' => $event->status,
            'keterangan' => $keterangan,
        ]);
    }
}
