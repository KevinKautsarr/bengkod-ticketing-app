@extends('layouts.admin_layouts')

@section('title', 'Manajemen Event')

@section('content')

    <div class="container mx-auto p-10">
        <div class="flex items-center gap-2">
            <h1 class="text-3xl font-semibold mb-4">Manajemen Event</h1>
            <div class="ml-auto flex gap-2">
                <a href="{{ route('admin.events.export', request()->query()) }}" class="btn btn-outline">Export Excel</a>
                <a href="{{ route('admin.events.create') }}" class="btn btn-primary">Tambah Event</a>
            </div>
        </div>

        @if (session('success'))
            <div class="alert alert-success shadow-lg mb-4">
                <div><span>{{ session('success') }}</span></div>
            </div>
        @endif

        @if (session('error'))
            <div class="alert alert-error shadow-lg mb-4">
                <div><span>{{ session('error') }}</span></div>
            </div>
        @endif

        <!-- Filter Form -->
        <div class="bg-white rounded-box p-5 shadow-xs mb-5">
            <form method="GET" action="{{ route('admin.events.index') }}" class="grid grid-cols-1 md:grid-cols-4 gap-4 items-end">
                <div class="form-control w-full">
                    <label class="label mb-2">
                        <span class="label-text">Cari Event</span>
                    </label>
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari judul atau lokasi..." class="input input-bordered w-full" />
                </div>

                <div class="form-control w-full">
                    <label class="label mb-2">
                        <span class="label-text">Kategori</span>
                    </label>
                    <select name="kategori_id" class="select select-bordered w-full">
                        <option value="">Semua Kategori</option>
                        @foreach ($categories as $kategori)
                            <option value="{{ $kategori->id }}" {{ request('kategori_id') == $kategori->id ? 'selected' : '' }}>
                                {{ $kategori->nama }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="form-control w-full">
                    <label class="label mb-2">
                        <span class="label-text">Urutkan Tanggal</span>
                    </label>
                    <select name="sort" class="select select-bordered w-full">
                        <option value="asc" {{ request('sort', 'asc') == 'asc' ? 'selected' : '' }}>Terlama - Terbaru</option>
                        <option value="desc" {{ request('sort') == 'desc' ? 'selected' : '' }}>Terbaru - Terlama</option>
                    </select>
                </div>

                <div class="flex gap-2">
                    <button type="submit" class="btn btn-primary">Filter</button>
                    <a href="{{ route('admin.events.index') }}" class="btn btn-outline">Reset</a>
                </div>
            </form>
        </div>

        <form method="POST" action="{{ route('admin.events.bulk-destroy') }}" id="bulk-delete-form" onsubmit="return confirm('Apakah Anda yakin ingin menghapus event terpilih?');">
            @csrf

            <div class="flex items-center justify-between mb-3">
                <span class="text-sm text-gray-500" id="selected-count">0 event dipilih</span>
                <button type="submit" class="btn btn-sm bg-red-500 text-white" id="bulk-delete-btn" disabled>Hapus Terpilih</button>
            </div>

            <div class="overflow-x-auto rounded-box bg-white p-5 shadow-xs">
                <table class="table">
                    <!-- head -->
                    <thead>
                        <tr>
                            <th>
                                <input type="checkbox" class="checkbox" id="select-all" />
                            </th>
                            <th>Gambar</th>
                            <th>Judul</th>
                            <th>Kategori</th>
                            <th>Tanggal</th>
                            <th>Lokasi</th>
                            <th>Status</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($events as $event)
                            <tr>
                                <td>
                                    <input type="checkbox" name="event_ids[]" value="{{ $event->id }}" class="checkbox event-checkbox" {{ $event->hasSales() ? 'disabled' : '' }} />
                                </td>
                                <td>
                                    <img src="{{ $event->image_url }}" alt="{{ $event->judul }}" class="w-16 h-16 object-cover rounded-lg" />
                                </td>
                                <td>{{ $event->judul }}</td>
                                <td>{{ $event->kategori->nama ?? '-' }}</td>
                                <td>{{ $event->tanggal_waktu->format('d M Y, H:i') }}</td>
                                <td>{{ $event->lokasi->nama_lokasi ?? '-' }}</td>
                                <td>
                                    @php
                                        $statusClass = match ($event->status) {
                                            'Upcoming' => 'badge-info',
                                            'Ongoing' => 'badge-success',
                                            'Completed' => 'badge-neutral',
                                            default => 'badge-ghost',
                                        };
                                    @endphp
                                    <span class="badge {{ $statusClass }}">{{ $event->status }}</span>
                                </td>
                                <td>
                                    <div class="flex gap-2">
                                        <a href="{{ route('events.show', $event) }}" class="btn btn-sm btn-outline">View</a>
                                        <a href="{{ route('admin.events.edit', $event) }}" class="btn btn-sm btn-primary">Edit</a>
                                        <button type="submit" form="clone-form-{{ $event->id }}" class="btn btn-sm btn-secondary">Duplikat</button>
                                        <button type="submit" form="delete-form-{{ $event->id }}" class="btn btn-sm bg-red-500 text-white">Hapus</button>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center">Tidak ada event tersedia.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </form>

        @foreach ($events as $event)
            <form method="POST" action="{{ route('admin.events.clone', $event) }}" id="clone-form-{{ $event->id }}" class="hidden">
                @csrf
            </form>
            <form method="POST" action="{{ route('admin.events.destroy', $event) }}" id="delete-form-{{ $event->id }}" class="hidden" onsubmit="return confirm('Apakah Anda yakin ingin menghapus event ini?');">
                @csrf
                @method('DELETE')
            </form>
        @endforeach

        <div class="mt-5">
            {{ $events->appends(request()->except('page'))->links() }}
        </div>
    </div>

    <script>
        const selectAll = document.getElementById('select-all');
        const checkboxes = document.querySelectorAll('.event-checkbox:not(:disabled)');
        const bulkDeleteBtn = document.getElementById('bulk-delete-btn');
        const selectedCount = document.getElementById('selected-count');

        function updateBulkState() {
            const checked = document.querySelectorAll('.event-checkbox:checked');
            selectedCount.textContent = checked.length + ' event dipilih';
            bulkDeleteBtn.disabled = checked.length === 0;
        }

        selectAll?.addEventListener('change', function () {
            checkboxes.forEach(cb => cb.checked = selectAll.checked);
            updateBulkState();
        });

        checkboxes.forEach(cb => cb.addEventListener('change', updateBulkState));
    </script>

@endsection
