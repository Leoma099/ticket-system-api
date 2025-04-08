<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Ticket extends Model
{
    use HasFactory;

    protected $fillable = [
        'account_id',
        'ticket_order',
        'full_name',
        'department',
        'subject',
        'priority_level',
        'status',
        'description',
        'request_date',
        'completed_date',
        'completed_time',
        'assigned_by',
        'approval_status',
        'approved_by',
        'approved_date',
    ];

    public function account()
    {
        return $this->belongsTo(Account::class, 'account_id');
    }

    public function ticketCreator()
    {
        return $this->belongsTo('App\Models\Account', 'account_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function customerFeedback()
    {
        return $this->hasOne(CustomerFeedback::class, 'ticket_id');
    }


}
