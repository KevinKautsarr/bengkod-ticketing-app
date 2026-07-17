<?php

namespace App\Exports;

use App\Models\Event;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class EventsExport implements FromCollection, WithHeadings, WithMapping, WithStyles
{
    public function __construct(protected array $filters = [])
    {
    }

    /**
     * @return \Illuminate\Support\Collection
     */
    public function collection()
    {
        $query = Event::with(['kategori', 'tikets', 'lokasi']);

        if (!empty($this->filters['kategori_id'])) {
            $query->where('kategori_id', $this->filters['kategori_id']);
        }

        if (!empty($this->filters['search'])) {
            $search = $this->filters['search'];
            $query->where(function ($q) use ($search) {
                $q->where('judul', 'like', "%{$search}%")
                    ->orWhereHas('lokasi', function ($sub) use ($search) {
                        $sub->where('nama_lokasi', 'like', "%{$search}%");
                    });
            });
        }

        $sort = $this->filters['sort'] ?? 'asc';

        return $query->orderBy('tanggal_waktu', $sort)->get();
    }

    public function headings(): array
    {
        return [
            'No',
            'Judul',
            'Kategori',
            'Tanggal & Waktu',
            'Lokasi',
            'Status',
            'Jumlah Tiket',
            'Total Stok',
        ];
    }

    public function map($event): array
    {
        static $no = 0;
        $no++;

        return [
            $no,
            $event->judul,
            $event->kategori->nama ?? '-',
            $event->tanggal_waktu->format('d M Y, H:i'),
            $event->lokasi->nama_lokasi ?? '-',
            $event->status,
            $event->tikets->count(),
            $event->tikets->sum('stok'),
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true]],
        ];
    }
}
