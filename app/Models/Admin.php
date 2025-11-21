<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Admin extends Model
{
    use HasFactory;

    protected $fillable = [
        'email_um6p',
        'department',
        'program',
        'user_id'
    ];

    /**
     * Get the user associated with the admin
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
