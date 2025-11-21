<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Student extends Model
{
    use HasFactory;

    protected $fillable = [
        'email_um6p',
        'health_insurance_number',
        'cin',
        'date_naissance',
        'ville',
        'etudes',
        'telephone',
        'user_id'
    ];

    protected $casts = [
        'date_naissance' => 'date',
    ];

    /**
     * Get the user associated with the student
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get all attendance records for the student
     */
    public function attendances(): HasMany
    {
        return $this->hasMany(Attendance::class);
    }
}
