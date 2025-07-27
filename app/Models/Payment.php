<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    use HasFactory;
    protected $guarded = [];

    public function purchase()
    {
        return $this->belongsTo(Purchase::class, 'transaction_id');
    }

    public function sale()
    {
        return $this->belongsTo(Sale::class, 'transaction_id');
    }
}
