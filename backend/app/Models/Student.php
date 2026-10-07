<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Contracts\Encryption\DecryptException;

class Student extends Model
{
    public const UPDATED_AT = null;

    protected $fillable = [
        'class_id', 'student_code', 'full_name', 'birth_year', 'medical_info_encrypted',
    ];

    protected $casts = ['birth_year' => 'integer'];

    public function schoolClass(): BelongsTo
    { return $this->belongsTo(SchoolClass::class, 'class_id'); }

    public function setMedicalInfo(string $plainText): void
    {
        $this->medical_info_encrypted = Crypt::encryptString($plainText);
    }

    public function getMedicalInfo(): ?string
    {
        if (!$this->medical_info_encrypted) return null;
        try { return Crypt::decryptString($this->medical_info_encrypted); }
        catch (DecryptException) { return null; }
    }
}
