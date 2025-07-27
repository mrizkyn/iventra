<?php namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
class Sale extends Model {
    use HasFactory; protected $guarded = [];
    public function customer() { return $this->belongsTo(Customer::class); }
    public function user() { return $this->belongsTo(User::class); }
    public function approver() { return $this->belongsTo(User::class, 'approver_id'); }
    public function details() { return $this->hasMany(SaleDetail::class); }
    public function payments() { return $this->hasMany(Payment::class, 'transaction_id')->where('transaction_type', 'Receivable'); }
    public function returns()
{
    return $this->hasMany(ReturnModel::class, 'related_transaction_id')->where('return_type', 'Sale');
}
}
