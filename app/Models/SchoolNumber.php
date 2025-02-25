<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SchoolNumber extends Model
{
    use HasFactory;

    protected $fillable = [
        'account_id',
        'number',
    ];

    public function account()
    {
        return $this->belongsTo(Account::class);
    }
}