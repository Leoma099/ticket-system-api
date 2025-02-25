<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;


class Account extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'school_number_id',
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

    public function schoolNumber()
    {
        return $this->belongsTo(SchoolNumber::class);
    }
}
