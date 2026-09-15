<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DokumenPublik extends Model
{
    use HasFactory;
    protected $table = 'tbl_dokumen_publik';
    protected $fillable = [
        'fkid_bidang',
        'nama_dokumen',
        'deskripsi_dokumen',
        'file_path',
        'file_name',
        'file_size',
        'file_type',
        'thumbnail_path',
    ];

    public function bidang(): BelongsTo
    {
        return $this->belongsTo(Bidang::class, 'fkid_bidang');
    }

    public function getFormattedSizeAttribute(): string
    {
        $bytes = $this->file_size ?? 0;
        if ($bytes >= 1048576) {
            return number_format($bytes / 1048576, 2) . ' MB';
        } elseif ($bytes >= 1024) {
            return number_format($bytes / 1024, 2) . ' KB';
        }
        return $bytes . ' bytes';
    }

    public function getCoverUrlAttribute(): ?string
    {
        if ($this->thumbnail_path && \Illuminate\Support\Facades\Storage::disk('public')->exists($this->thumbnail_path)) {
            return \Illuminate\Support\Facades\Storage::disk('public')->url($this->thumbnail_path);
        }
        return null;
    }
}
