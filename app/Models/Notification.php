<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Notification extends Model
{
    use HasFactory;

    protected $fillable = [
        'account_id',
        'title',
        'message',
        'is_read'
    ];

    public function account()
    {
        return $this->hasOne(Account::class, 'account_id');
    }
}
