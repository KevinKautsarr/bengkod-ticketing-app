@extends('layouts.admin_layouts')

@section('title', 'Management Lokasi')

@section('content')

    <div class="container mx-auto p-10">
        <div class="flex items-center mb-6">
            <h1 class="text-3xl font-semibold">Management Lokasi</h1>
            <button class="btn btn-primary ml-auto" onclick="add_modal.showModal()">Tambah Lokasi</button>
        </div>

        <!-- Search input -->
        <div class="mb-4">
            <form method="GET" action="{{ route('admin.lokasi.index') }}" class="flex gap-2">
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari nama lokasi..." class="input input-bordered w-full max-w-xs" />
                <button type="submit" class="btn btn-primary">Filter</button>
                @if(request('search'))
                    <a href="{{ route('admin.lokasi.index') }}" class="btn btn-ghost">Reset</a>
                @endif
            </form>
        </div>

        <div class="overflow-x-auto rounded-box bg-white p-5 shadow-xs">
            <table class="table">
                <!-- head -->
                <thead>
                    <tr>
                        <th>No</th>
                        <th class="w-3/4">Nama Lokasi</th>
                        <th>Status</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($lokasis as $index => $lokasi)
                    <tr>
                        <th>{{ ($lokasis->currentPage() - 1) * $lokasis->perPage() + $index + 1 }}</th>
                        <td>{{ $lokasi->nama_lokasi }}</td>
                        <td>
                            <span class="badge badge-success text-white">Aktif</span>
                        </td>
                        <td>
                            <div class="flex gap-2">
                                <button class="btn btn-sm btn-primary" onclick="openEditModal(this)" data-id="{{ $lokasi->id }}" data-nama="{{ $lokasi->nama_lokasi }}">Edit</button>
                                <button class="btn btn-sm bg-red-500 text-white" onclick="openDeleteModal(this)" data-id="{{ $lokasi->id }}">Hapus</button>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="4" class="text-center py-4">Tidak ada lokasi tersedia.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        <div class="mt-4">
            {{ $lokasis->links() }}
        </div>
    </div>

    <!-- Add Location Modal -->
    <dialog id="add_modal" class="modal">
        <form method="POST" action="{{ route('admin.lokasi.store') }}" class="modal-box">
            @csrf
            <h3 class="text-lg font-bold mb-4">Tambah Lokasi</h3>
            <div class="form-control w-full mb-4">
                <label class="label mb-2">
                    <span class="label-text">Nama Lokasi</span>
                </label>
                <input type="text" placeholder="Masukkan nama lokasi" class="input input-bordered w-full" name="nama_lokasi" required />
            </div>
            <div class="modal-action">
                <button class="btn btn-primary" type="submit">Simpan</button>
                <button class="btn" onclick="add_modal.close()" type="reset">Batal</button>
            </div>
        </form>
    </dialog>

    <!-- Edit Location Modal -->
    <dialog id="edit_modal" class="modal">
        <form method="POST" class="modal-box">
            @csrf
            @method('PUT')

            <input type="hidden" name="lokasi_id" id="edit_lokasi_id">

            <h3 class="text-lg font-bold mb-4">Edit Lokasi</h3>
            <div class="form-control w-full mb-4">
                <label class="label mb-2">
                    <span class="label-text">Nama Lokasi</span>
                </label>
                <input type="text" placeholder="Masukkan nama lokasi" class="input input-bordered w-full" id="edit_lokasi_name" name="nama_lokasi" required />
            </div>
            <div class="modal-action">
                <button class="btn btn-primary" type="submit">Simpan</button>
                <button class="btn" onclick="edit_modal.close()" type="reset">Batal</button>
            </div>
        </form>
    </dialog>

    <!-- Delete Location Modal -->
    <dialog id="delete_modal" class="modal">
        <form method="POST" class="modal-box">
            @csrf
            @method('DELETE')

            <input type="hidden" name="lokasi_id" id="delete_lokasi_id">

            <h3 class="text-lg font-bold mb-4 text-red-600">Hapus Lokasi (Soft Delete)</h3>
            <p>Apakah Anda yakin ingin menghapus lokasi ini?</p>
            <div class="modal-action">
                <button class="btn bg-red-500 text-white" type="submit">Hapus</button>
                <button class="btn" onclick="delete_modal.close()" type="reset">Batal</button>
            </div>
        </form>
    </dialog>

    <script>
        function openEditModal(button) {
            const name = button.dataset.nama;
            const id = button.dataset.id;
            const form = document.querySelector('#edit_modal form');
            
            document.getElementById("edit_lokasi_name").value = name;
            document.getElementById("edit_lokasi_id").value = id;

            // Set action with ID
            form.action = `{{ url('/admin/lokasi') }}/${id}`;

            edit_modal.showModal();
        }

        function openDeleteModal(button) {
            const id = button.dataset.id;
            const form = document.querySelector('#delete_modal form');
            document.getElementById("delete_lokasi_id").value = id;

            // Set action with ID
            form.action = `{{ url('/admin/lokasi') }}/${id}`;

            delete_modal.showModal();
        }
    </script>

@endsection
