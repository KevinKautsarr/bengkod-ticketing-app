@extends('layouts.admin_layouts')

@section('title', 'Tambah Event')

@section('content')

    <div class="container mx-auto p-10">
        <div class="mb-4">
            <a href="{{ route('admin.events.index') }}" class="btn btn-outline btn-sm">← Kembali</a>
        </div>

        <div class="bg-white rounded-box p-6 shadow-xs">
            <h1 class="text-2xl font-semibold mb-6">Tambah Event</h1>

            <form method="POST" action="{{ route('admin.events.store') }}" enctype="multipart/form-data">
                @csrf

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="space-y-2">
                        <label class="block">
                            <span class="text-sm font-medium">Judul Event</span>
                            <span class="text-error">*</span>
                        </label>
                        <input type="text" name="judul" value="{{ old('judul') }}" class="input input-bordered w-full" required>
                        @error('judul')
                            <span class="text-error text-sm">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="space-y-2">
                        <label class="block">
                            <span class="text-sm font-medium">Kategori</span>
                            <span class="text-error">*</span>
                        </label>
                        <select name="kategori_id" class="select select-bordered w-full" required>
                            <option value="">Pilih Kategori</option>
                            @foreach ($categories as $kategori)
                                <option value="{{ $kategori->id }}" {{ old('kategori_id') == $kategori->id ? 'selected' : '' }}>
                                    {{ $kategori->nama }}
                                </option>
                            @endforeach
                        </select>
                        @error('kategori_id')
                            <span class="text-error text-sm">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="space-y-2">
                        <label class="block">
                            <span class="text-sm font-medium">Lokasi</span>
                            <span class="text-error">*</span>
                        </label>
                        <input type="text" name="lokasi" value="{{ old('lokasi') }}" class="input input-bordered w-full" required>
                        @error('lokasi')
                            <span class="text-error text-sm">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="space-y-2">
                        <label class="block">
                            <span class="text-sm font-medium">Tanggal & Waktu</span>
                            <span class="text-error">*</span>
                        </label>
                        <input type="datetime-local" name="tanggal_waktu" value="{{ old('tanggal_waktu') }}" class="input input-bordered w-full" required>
                        @error('tanggal_waktu')
                            <span class="text-error text-sm">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="space-y-2">
                        <label class="block">
                            <span class="text-sm font-medium">Gambar</span>
                        </label>
                        <input type="file" name="gambar" id="gambar" accept=".jpg,.jpeg,.png" class="file-input file-input-bordered w-full">
                        <span class="text-xs text-gray-500">Maksimal 2MB. Kosongkan untuk menggunakan gambar default.</span>
                        @error('gambar')
                            <span class="text-error text-sm">{{ $message }}</span>
                        @enderror
                        <div id="image_preview_container" class="hidden mt-2">
                            <img id="image_preview" src="" alt="Preview" class="w-32 h-32 object-cover rounded-lg">
                        </div>
                    </div>

                    <div class="space-y-2 md:col-span-2">
                        <label class="block">
                            <span class="text-sm font-medium">Deskripsi</span>
                            <span class="text-error">*</span>
                        </label>
                        <textarea name="deskripsi" rows="4" class="textarea textarea-bordered w-full" required>{{ old('deskripsi') }}</textarea>
                        @error('deskripsi')
                            <span class="text-error text-sm">{{ $message }}</span>
                        @enderror
                    </div>
                </div>

                <!-- Dynamic Ticket Form -->
                <div class="mt-8">
                    <div class="flex justify-between items-center mb-4">
                        <h2 class="text-lg font-semibold">Tiket</h2>
                        <button type="button" id="add_tiket_btn" class="btn btn-primary btn-sm">Tambah Tiket</button>
                    </div>

                    <div id="tikets_container" class="space-y-4"></div>
                    @error('tikets')
                        <span class="text-error text-sm">{{ $message }}</span>
                    @enderror
                </div>

                <div class="mt-8 flex gap-2">
                    <button type="submit" class="btn btn-primary">Simpan</button>
                    <a href="{{ route('admin.events.index') }}" class="btn btn-outline">Batal</a>
                </div>
            </form>
        </div>
    </div>

    <script>
        let tiketIndex = 0;

        function renderTiketCard(index) {
            return `
                <div class="card bg-base-200" id="tiket_card_${index}">
                    <div class="card-body p-4">
                        <div class="flex justify-between items-center mb-2">
                            <h3 class="font-medium">Tiket #${index + 1}</h3>
                            <button type="button" class="btn btn-sm btn-error text-white" onclick="removeTiket(${index})">Hapus</button>
                        </div>
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <div class="space-y-2">
                                <label class="block">
                                    <span class="text-sm font-medium">Tipe Tiket</span>
                                    <span class="text-error">*</span>
                                </label>
                                <select name="tikets[${index}][tipe]" class="select select-bordered w-full" required>
                                    <option value="reguler">Reguler</option>
                                    <option value="premium">Premium</option>
                                </select>
                            </div>
                            <div class="space-y-2">
                                <label class="block">
                                    <span class="text-sm font-medium">Harga</span>
                                    <span class="text-error">*</span>
                                </label>
                                <input type="number" name="tikets[${index}][harga]" min="0" class="input input-bordered w-full" required>
                            </div>
                            <div class="space-y-2">
                                <label class="block">
                                    <span class="text-sm font-medium">Stok</span>
                                    <span class="text-error">*</span>
                                </label>
                                <input type="number" name="tikets[${index}][stok]" min="0" class="input input-bordered w-full" required>
                            </div>
                        </div>
                    </div>
                </div>
            `;
        }

        function addTiket() {
            const container = document.getElementById('tikets_container');
            container.insertAdjacentHTML('beforeend', renderTiketCard(tiketIndex));
            tiketIndex++;
        }

        function removeTiket(index) {
            const card = document.getElementById(`tiket_card_${index}`);
            if (card) {
                card.remove();
            }
        }

        document.getElementById('add_tiket_btn').addEventListener('click', addTiket);

        document.getElementById('gambar').addEventListener('change', function (e) {
            const file = e.target.files[0];
            const previewContainer = document.getElementById('image_preview_container');
            const preview = document.getElementById('image_preview');

            if (file) {
                const reader = new FileReader();
                reader.onload = function (event) {
                    preview.src = event.target.result;
                    previewContainer.classList.remove('hidden');
                };
                reader.readAsDataURL(file);
            } else {
                previewContainer.classList.add('hidden');
            }
        });

        // Add 1 ticket by default
        addTiket();
    </script>

@endsection
