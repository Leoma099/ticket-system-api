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
        'photo',
        'assigned_by',
        'approval_status',
        'approved_by',
        'approved_date',
    ];

    public $statusOptions = [
        1 => 'For Approval',
        2 => 'In Progress',
        3 => 'Resolved',
        4 => 'Unresolved',
    ];

    public function account()
    {
        return $this->hasOne(Account::class, 'user_id');
    }

    public function ticketCreator()
    {
        return $this->belongsTo('App\Models\Account', 'account_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // public function toArray()
    // {
    //     return [
    //         'id' => $this->id,
    //         'ticket_order' => $this->ticket_order,
    //         'full_name' => $this->full_name,
    //         'subject' => $this->subject,
    //         'department' => $this->department,
    //         'priority_level' => $this->priority_level,
    //         'status' => $this->status,
    //         'photo' => $this->photo,
    //         'description' => $this->description,
    //         'request_date' => $this->request_date,
    //         'completed_date' => $this->completed_date,
    //         'approval_status' => $this->approval_status,
    //     ];
    // }

}
