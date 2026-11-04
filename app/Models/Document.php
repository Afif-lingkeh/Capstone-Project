<?php
// app/Models/Document.php

namespace App\Models;

use App\Enums\DocumentCategory;
use App\Enums\DocumentStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

// Berkas santri. File fisik ada di disk privat "local"; jalur file tidak pernah dikirim ke browser.
class Document extends Model
{
    public const DISK = 'local';

    protected $fillable = ['owner_id', 'uploaded_by', 'category', 'title', 'original_name', 'path', 'mime_type', 'size', 'note', 'status', 'reviewed_by', 'reviewed_at', 'review_note'];

    protected $hidden = ['path'];

    protected function casts(): array
    {
        return [
            'category' => DocumentCategory::class,
            'status' => DocumentStatus::class,
            'reviewed_at' => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        // Hapus file fisik ketika catatannya dihapus.
        static::deleted(fn (Document $document) => Storage::disk(self::DISK)->delete($document->path));
    }

    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    public function uploader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    public function isImage(): bool
    {
        return str_starts_with($this->mime_type, 'image/');
    }

    public function isPdf(): bool
    {
        return $this->mime_type === 'application/pdf';
    }

    public function icon(): string
    {
        return $this->isImage() ? 'file-image' : ($this->isPdf() ? 'file-text' : 'paperclip');
    }

    public function humanSize(): string
    {
        return $this->size >= 1048576
            ? number_format($this->size / 1048576, 1, ',', '.').' MB'
            : max(1, (int) round($this->size / 1024)).' KB';
    }

    public function extension(): string
    {
        return strtoupper(pathinfo($this->original_name, PATHINFO_EXTENSION));
    }
}
