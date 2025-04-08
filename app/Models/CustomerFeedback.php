<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CustomerFeedback extends Model
{
    use HasFactory;

    protected $fillable =
    [
        'account_id',
        'ticket_id',  
        'ticket_order',
        'full_name',
        'assigned_by',
        'completed_date',
        'completed_time',
        'rate',
        'comment',
    ];

    public function Account()
    {
        return $this->belongsTo(Account::class, 'account_id', 'id');
    }
    
    public function Ticket()
    {
        return $this->belongsTo(Ticket::class, 'ticket_id', 'id');
    }

    public function CustomerFeedbackCreator()
    {
        return $this->belongsTo(Account::class, 'account_id');
    }
}
