<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class EventFormRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user() && $this->user()->role === 'admin';
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'judul' => 'required|string|max:255',
            'deskripsi' => 'required|string',
            'lokasi' => 'required|string|max:255',
            'kategori_id' => 'required|exists:kategoris,id',
            'tanggal_waktu' => 'required|date|after:now',
            'gambar' => 'nullable|image|mimes:jpg,jpeg,png|max:2048',

            'tikets' => 'required|array|min:1',
            'tikets.*.tipe' => 'required|in:reguler,premium',
            'tikets.*.harga' => 'required|numeric|min:0',
            'tikets.*.stok' => 'required|integer|min:0',
            'tikets.*.id' => 'nullable|exists:tikets,id',
        ];
    }

    /**
     * Get the custom error messages for validation rules.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'judul.required' => 'Judul event wajib diisi.',
            'judul.string' => 'Judul event harus berupa teks.',
            'judul.max' => 'Judul event maksimal 255 karakter.',

            'deskripsi.required' => 'Deskripsi event wajib diisi.',
            'deskripsi.string' => 'Deskripsi event harus berupa teks.',

            'lokasi.required' => 'Lokasi event wajib diisi.',
            'lokasi.string' => 'Lokasi event harus berupa teks.',
            'lokasi.max' => 'Lokasi event maksimal 255 karakter.',

            'kategori_id.required' => 'Kategori event wajib dipilih.',
            'kategori_id.exists' => 'Kategori yang dipilih tidak valid.',

            'tanggal_waktu.required' => 'Tanggal & waktu event wajib diisi.',
            'tanggal_waktu.date' => 'Tanggal & waktu event tidak valid.',
            'tanggal_waktu.after' => 'Tanggal & waktu event harus setelah waktu saat ini.',

            'gambar.image' => 'File yang diupload harus berupa gambar.',
            'gambar.mimes' => 'Gambar harus berformat jpg, jpeg, atau png.',
            'gambar.max' => 'Ukuran gambar maksimal 2MB.',

            'tikets.required' => 'Minimal harus ada 1 tiket.',
            'tikets.array' => 'Data tiket tidak valid.',
            'tikets.min' => 'Minimal harus ada 1 tiket.',

            'tikets.*.tipe.required' => 'Tipe tiket wajib dipilih.',
            'tikets.*.tipe.in' => 'Tipe tiket harus reguler atau premium.',

            'tikets.*.harga.required' => 'Harga tiket wajib diisi.',
            'tikets.*.harga.numeric' => 'Harga tiket harus berupa angka.',
            'tikets.*.harga.min' => 'Harga tiket tidak boleh kurang dari 0.',

            'tikets.*.stok.required' => 'Stok tiket wajib diisi.',
            'tikets.*.stok.integer' => 'Stok tiket harus berupa angka bulat.',
            'tikets.*.stok.min' => 'Stok tiket tidak boleh kurang dari 0.',

            'tikets.*.id.exists' => 'Tiket yang dipilih tidak valid.',
        ];
    }
}
