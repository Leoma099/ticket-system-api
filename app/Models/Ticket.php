<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Ticket extends Model
{
    use HasFactory;

    protected $fillable = [
        'account_id',
        'full_name',
        'department',
        'subject',
        'priority_level',
        'status',
        'description',
        'request_date',
        'completed_date',
        'photo',
        'assigned_by',
    ];

    public function account()
    {
        return $this->hasOne(Account::class, 'user_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

}
