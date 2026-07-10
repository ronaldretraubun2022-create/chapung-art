<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Certificate extends Model
{
    protected $fillable = [
        'artwork_id',
        'artist_id',
        'certificate_number',
        'owner_name',
        'issued_at',
        'qr_code_path',
        'pdf_path',
        'notes',
        'is_verified',
    ];

    protected $casts = [
        'issued_at' => 'date',
        'is_verified' => 'boolean',
    ];

    protected static function booted(): void
    {
        static::creating(function (Certificate $certificate): void {
            if (blank($certificate->certificate_number)) {
                $certificate->certificate_number = static::generateCertificateNumber();
            }

            if (blank($certificate->qr_code_path)) {
                $certificate->qr_code_path = static::verificationPath($certificate->certificate_number);
            }
        });
    }

    public function artwork(): BelongsTo
    {
        return $this->belongsTo(Artwork::class);
    }

    public function artist(): BelongsTo
    {
        return $this->belongsTo(Artist::class);
    }

    public function getVerificationUrlAttribute(): string
    {
        return url($this->qr_code_path ?: static::verificationPath($this->certificate_number));
    }

    public static function generateCertificateNumber(): string
    {
        do {
            $number = 'COA-'.now()->format('Ymd').'-'.str_pad((string) random_int(1, 99999), 5, '0', STR_PAD_LEFT);
        } while (static::where('certificate_number', $number)->exists());

        return $number;
    }

    public static function verificationPath(string $certificateNumber): string
    {
        return '/certificates/verify/'.$certificateNumber;
    }
}
