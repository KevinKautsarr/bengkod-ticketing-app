@extends('layouts.admin_layouts')

@section('title', 'Edit Event')

@section('content')

    <div class="container mx-auto p-10">
        <div class="mb-4">
            <a href="{{ route('admin.events.index') }}" class="btn btn-outline btn-sm">← Kembali</a>
        </div>

        <div class="bg-white rounded-box p-6 shadow-xs">
            <h1 class="text-2xl font-semibold mb-6">Edit Event</h1>

            @if ($hasSales)
                <div class="alert alert-warning shadow-lg mb-6">
                    <div><span>Peringatan: Event ini sudah memiliki penjualan tiket. Beberapa field mungkin tidak dapat diubah.</span></div>
                </div>
            @endif

            <form method="POST" action="{{ route('admin.events.update', $event) }}" enctype="multipart/form-data">
                @csrf
                @method('PUT')

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="space-y-2">
                        <label class="block">
                            <span class="text-sm font-medium">Judul Event</span>
                            <span class="text-error">*</span>
                        </label>
                        <input type="text" name="judul" value="{{ old('judul', $event->judul) }}" class="input input-bordered w-full" required>
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
                            @foreach ($categories as $kategori)
                                <option value="{{ $kategori->id }}" {{ old('kategori_id', $event->kategori_id) == $kategori->id ? 'selected' : '' }}>
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
                        <input type="text" name="lokasi" value="{{ old('lokasi', $event->lokasi) }}" class="input input-bordered w-full" required>
                        @error('lokasi')
                            <span class="text-error text-sm">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="space-y-2">
                        <label class="block">
                            <span class="text-sm font-medium">Tanggal & Waktu</span>
                            <span class="text-error">*</span>
                            @if ($hasSales)
                                <span class="text-xs text-warning">(tidak dapat diubah)</span>
                            @endif
                        </label>
                        <input type="datetime-local" name="tanggal_waktu"
                               value="{{ old('tanggal_waktu', $event->tanggal_waktu->format('Y-m-d\TH:i')) }}"
                               class="input input-bordered w-full"
                               {{ $hasSales ? 'readonly' : '' }}
                               required>
                        @error('tanggal_waktu')
                            <span class="text-error text-sm">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="space-y-2">
                        <label class="block">
                            <span class="text-sm font-medium">Gambar</span>
                        </label>

                        <div class="mb-2">
                            <img src="{{ $event->image_url }}" alt="{{ $event->judul }}" class="w-32 h-32 object-cover rounded-lg">
                        </div>

                        <input type="file" name="gambar" id="gambar" accept=".jpg,.jpeg,.png" class="file-input file-input-bordered w-full">
                        <span class="text-xs text-gray-500">Kosongkan jika tidak ingin mengubah gambar. Maksimal 2MB. Gambar akan bisa dipotong (crop) sebelum diunggah.</span>
                        @error('gambar')
                            <span class="text-error text-sm">{{ $message }}</span>
                        @enderror
                        <div id="image_preview_container" class="hidden mt-2">
                            <img id="image_preview" src="" alt="Preview" class="w-32 h-32 object-cover rounded-lg">
                            <button type="button" id="recrop_btn" class="btn btn-xs btn-outline mt-2">Crop Ulang</button>
                        </div>
                    </div>

                    <div class="space-y-2 md:col-span-2">
                        <label class="block">
                            <span class="text-sm font-medium">Deskripsi</span>
                            <span class="text-error">*</span>
                        </label>
                        <textarea name="deskripsi" rows="4" class="textarea textarea-bordered w-full" required>{{ old('deskripsi', $event->deskripsi) }}</textarea>
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

        <!-- Riwayat Perubahan (Status History) -->
        @if ($event->histories->isNotEmpty())
            <div class="bg-white rounded-box p-6 shadow-xs mt-6">
                <h2 class="text-lg font-semibold mb-4">Riwayat Perubahan</h2>

                <ul class="timeline timeline-vertical">
                    @foreach ($event->histories as $history)
                        <li>
                            @if (!$loop->first)
                                <hr />
                            @endif
                            <div class="timeline-start text-sm text-gray-500">
                                {{ $history->created_at->format('d M Y, H:i') }}
                            </div>
                            <div class="timeline-middle">
                                <div class="badge badge-sm {{ match($history->action) {
                                    'created' => 'badge-success',
                                    'updated' => 'badge-info',
                                    'cloned' => 'badge-secondary',
                                    'status_changed' => 'badge-warning',
                                    default => 'badge-ghost',
                                } }}">{{ $history->action === 'status_changed' ? 'Status: ' . $history->status : ucfirst($history->action) }}</div>
                            </div>
                            <div class="timeline-end timeline-box">
                                <p class="text-sm">{{ $history->keterangan }}</p>
                                @if ($history->action !== 'status_changed')
                                    <p class="text-xs text-gray-400 mt-1">
                                        oleh {{ $history->user->name ?? 'Sistem' }} &middot; status saat itu: {{ $history->status }}
                                    </p>
                                @endif
                            </div>
                            @if (!$loop->last)
                                <hr />
                            @endif
                        </li>
                    @endforeach
                </ul>
            </div>
        @endif
    </div>

    <!-- Crop Image Modal -->
    <dialog id="crop_modal" class="modal">
        <div class="modal-box max-w-2xl">
            <h3 class="text-lg font-bold mb-4">Potong Gambar</h3>
            <div class="max-h-[60vh] overflow-hidden">
                <img id="crop_image" src="" alt="Crop preview" class="max-w-full">
            </div>
            <div class="modal-action">
                <button type="button" class="btn" id="crop_cancel_btn">Batal</button>
                <button type="button" class="btn btn-primary" id="crop_apply_btn">Terapkan</button>
            </div>
        </div>
    </dialog>

    <link href="https://cdn.jsdelivr.net/npm/cropperjs@1.6.2/dist/cropper.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/cropperjs@1.6.2/dist/cropper.min.js"></script>

    @php
        $tiketsData = $event->tikets->map(function ($tiket) {
            return [
                'id' => $tiket->id,
                'tipe' => $tiket->tipe,
                'harga' => $tiket->harga,
                'stok' => $tiket->stok,
                'sudah_terjual' => $tiket->orders()->exists(),
            ];
        });
    @endphp

    <script>
        let tiketIndex = 0;

        const existingTikets = @json($tiketsData);

        function renderTiketCard(index, data = null) {
            const id = data?.id ?? '';
            const tipe = data?.tipe ?? 'reguler';
            const harga = data?.harga ?? '';
            const stok = data?.stok ?? '';
            const sudahTerjual = data?.sudah_terjual ?? false;

            return `
                <div class="card bg-base-200" id="tiket_card_${index}">
                    <div class="card-body p-4">
                        <div class="flex justify-between items-center mb-2">
                            <h3 class="font-medium">
                                Tiket #${index + 1}
                                ${sudahTerjual ? '<span class="badge badge-warning ml-2">Sudah Terjual</span>' : ''}
                            </h3>
                            ${sudahTerjual ? '' : `<button type="button" class="btn btn-sm btn-error text-white" onclick="removeTiket(${index})">Hapus</button>`}
                        </div>
                        <input type="hidden" name="tikets[${index}][id]" value="${id}">
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <div class="space-y-2">
                                <label class="block">
                                    <span class="text-sm font-medium">Tipe Tiket</span>
                                    <span class="text-error">*</span>
                                </label>
                                <select name="tikets[${index}][tipe]" class="select select-bordered w-full" required>
                                    <option value="reguler" ${tipe === 'reguler' ? 'selected' : ''}>Reguler</option>
                                    <option value="premium" ${tipe === 'premium' ? 'selected' : ''}>Premium</option>
                                </select>
                            </div>
                            <div class="space-y-2">
                                <label class="block">
                                    <span class="text-sm font-medium">Harga</span>
                                    <span class="text-error">*</span>
                                </label>
                                <input type="number" name="tikets[${index}][harga]" value="${harga}" min="0" class="input input-bordered w-full" required>
                            </div>
                            <div class="space-y-2">
                                <label class="block">
                                    <span class="text-sm font-medium">Stok</span>
                                    <span class="text-error">*</span>
                                </label>
                                <input type="number" name="tikets[${index}][stok]" value="${stok}" min="0" class="input input-bordered w-full" required>
                            </div>
                        </div>
                    </div>
                </div>
            `;
        }

        function addTiket(data = null) {
            const container = document.getElementById('tikets_container');
            container.insertAdjacentHTML('beforeend', renderTiketCard(tiketIndex, data));
            tiketIndex++;
        }

        function removeTiket(index) {
            const card = document.getElementById(`tiket_card_${index}`);
            if (card) {
                card.remove();
            }
        }

        document.getElementById('add_tiket_btn').addEventListener('click', () => addTiket());

        const gambarInput = document.getElementById('gambar');
        const previewContainer = document.getElementById('image_preview_container');
        const preview = document.getElementById('image_preview');
        const cropImage = document.getElementById('crop_image');
        const cropModal = document.getElementById('crop_modal');
        let cropper = null;
        let lastSelectedFileName = 'gambar.jpg';

        gambarInput.addEventListener('change', function (e) {
            const file = e.target.files[0];
            if (!file) {
                previewContainer.classList.add('hidden');
                return;
            }

            lastSelectedFileName = file.name;
            const reader = new FileReader();
            reader.onload = function (event) {
                cropImage.src = event.target.result;
                cropModal.showModal();

                if (cropper) {
                    cropper.destroy();
                }
                cropper = new Cropper(cropImage, {
                    aspectRatio: NaN,
                    viewMode: 1,
                    autoCropArea: 1,
                });
            };
            reader.readAsDataURL(file);
        });

        document.getElementById('crop_apply_btn').addEventListener('click', function () {
            if (!cropper) return;

            cropper.getCroppedCanvas().toBlob(function (blob) {
                const croppedFile = new File([blob], lastSelectedFileName, { type: blob.type });
                const dataTransfer = new DataTransfer();
                dataTransfer.items.add(croppedFile);
                gambarInput.files = dataTransfer.files;

                preview.src = URL.createObjectURL(blob);
                previewContainer.classList.remove('hidden');

                cropModal.close();
                cropper.destroy();
                cropper = null;
            });
        });

        document.getElementById('crop_cancel_btn').addEventListener('click', function () {
            cropModal.close();
            if (cropper) {
                cropper.destroy();
                cropper = null;
            }
            gambarInput.value = '';
            previewContainer.classList.add('hidden');
        });

        document.getElementById('recrop_btn').addEventListener('click', function () {
            if (!gambarInput.files.length) return;

            const file = gambarInput.files[0];
            const reader = new FileReader();
            reader.onload = function (event) {
                cropImage.src = event.target.result;
                cropModal.showModal();

                if (cropper) {
                    cropper.destroy();
                }
                cropper = new Cropper(cropImage, {
                    aspectRatio: NaN,
                    viewMode: 1,
                    autoCropArea: 1,
                });
            };
            reader.readAsDataURL(file);
        });

        // Load existing tickets
        existingTikets.forEach(function (tiket) {
            addTiket(tiket);
        });
    </script>

@endsection
