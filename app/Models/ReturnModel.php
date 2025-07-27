<?php namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ReturnModel extends Model {
    use HasFactory;
    protected $table = 'returns';
    protected $guarded = [];

    public function details() {
        return $this->hasMany(ReturnDetail::class, 'return_id');
    }

    // Relasi baru
    public function purchase() {
        return $this->belongsTo(Purchase::class, 'related_transaction_id');
    }

    // Relasi baru
    public function sale() {
        return $this->belongsTo(Sale::class, 'related_transaction_id');
    }
}
