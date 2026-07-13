@extends('layouts.admin_layouts')

@section('title', 'Manajemen Event')

@section('content')

    <div class="container mx-auto p-10">
        <div class="flex">
            <h1 class="text-3xl font-semibold mb-4">Manajemen Event</h1>
            <a href="{{ route('admin.events.create') }}" class="btn btn-primary ml-auto">Tambah Event</a>
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

        <div class="overflow-x-auto rounded-box bg-white p-5 shadow-xs">
            <table class="table">
                <!-- head -->
                <thead>
                    <tr>
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
                                <img src="{{ $event->image_url }}" alt="{{ $event->judul }}" class="w-16 h-16 object-cover rounded-lg" />
                            </td>
                            <td>{{ $event->judul }}</td>
                            <td>{{ $event->kategori->nama ?? '-' }}</td>
                            <td>{{ $event->tanggal_waktu->format('d M Y, H:i') }}</td>
                            <td>{{ $event->lokasi }}</td>
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
                                    <form method="POST" action="{{ route('admin.events.destroy', $event) }}" onsubmit="return confirm('Apakah Anda yakin ingin menghapus event ini?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm bg-red-500 text-white">Hapus</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center">Tidak ada event tersedia.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-5">
            {{ $events->appends(request()->except('page'))->links() }}
        </div>
    </div>

@endsection
