<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

class Event extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'kategori_id',
        'judul',
        'deskripsi',
        'lokasi',
        'gambar',
        'tanggal_waktu',
    ];

    protected $casts = [
        'tanggal_waktu' => 'datetime',
    ];

    public function tikets()
    {
        return $this->hasMany(Tiket::class);
    }

    public function kategori()
    {
        return $this->belongsTo(Kategori::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function orders()
    {
        return $this->hasMany(Order::class);
    }

    public function histories()
    {
        return $this->hasMany(EventHistory::class)->latest();
    }

    public function getStatusAttribute()
    {
        $now = Carbon::now();
        $mulai = $this->tanggal_waktu;
        $selesai = $mulai->copy()->addHours(3);

        if ($now->lt($mulai)) {
            return 'Upcoming';
        }

        if ($now->between($mulai, $selesai)) {
            return 'Ongoing';
        }

        return 'Completed';
    }

    public function hasSales()
    {
        return $this->orders()->exists();
    }

    /**
     * Record a history entry if the computed status has changed
     * since the last recorded status-change entry.
     */
    public function recordStatusChangeIfNeeded(): void
    {
        $currentStatus = $this->status;

        $lastStatusChange = $this->histories()
            ->where('action', 'status_changed')
            ->first();

        if ($lastStatusChange && $lastStatusChange->status === $currentStatus) {
            return;
        }

        $this->histories()->create([
            'user_id' => null,
            'action' => 'status_changed',
            'status' => $currentStatus,
            'keterangan' => "Status event berubah menjadi \"{$currentStatus}\".",
        ]);
    }

    public function scopeUpcoming($query)
    {
        return $query->where('tanggal_waktu', '>', Carbon::now());
    }

    public function scopeOngoing($query)
    {
        $now = Carbon::now();

        return $query->where('tanggal_waktu', '<=', $now)
            ->where('tanggal_waktu', '>=', $now->copy()->subHours(3));
    }

    public function scopeCompleted($query)
    {
        return $query->where('tanggal_waktu', '<', Carbon::now()->subHours(3));
    }

    public function getImageUrlAttribute()
    {
        if ($this->gambar && filter_var($this->gambar, FILTER_VALIDATE_URL)) {
            return $this->gambar;
        }

        if ($this->gambar && file_exists(public_path('storage/' . $this->gambar))) {
            return asset('storage/' . $this->gambar);
        }

        return asset('storage/konser.jpg');
    }
}
