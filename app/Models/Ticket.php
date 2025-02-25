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
        'department', // 1 = CCS, 2 = CBA, etc.
        'subject', // 1 = Internet, 2 = Monitor, etc.
        'priority_level', // 1 = Low, 2 = Medium, 3 = High, 4 = Urgent
        'status', // 1 = Pending, 2 = In Progress, 3 = Reject, 4 = Pause
        'description',
        'request_date',
        'completed_date',
    ];
}
