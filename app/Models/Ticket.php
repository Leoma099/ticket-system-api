<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Ticket extends Model
{
    use HasFactory;

    protected $fillable = [
        'full_name',
        'school_number',
        'department',
        'subject',
        'priority_level',
        'status',
        'description',
        'request_date',
        'completed_date',
        'photo',
    ];
}
