<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;


class Account extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'full_name',
        'email',
        'date_of_birth',
        'address',
        'mobile_number',
        'photo'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function getPhotoAttribute($value)
    {
        // Ensure it only prepends "uploads/photos/" if it's not already included
        if ($value && !str_starts_with($value, 'uploads/photos/')) {
            return "uploads/photos/" . $value;
        }
        
        return $value;
    }

}
