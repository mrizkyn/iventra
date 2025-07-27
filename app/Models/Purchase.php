<?php namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
class Purchase extends Model {
    use HasFactory; protected $guarded = [];
    public function supplier() { return $this->belongsTo(Supplier::class); }
    public function user() { return $this->belongsTo(User::class); }
    public function approver() { return $this->belongsTo(User::class, 'approver_id'); }
    public function details() { return $this->hasMany(PurchaseDetail::class); }
    public function payments() { return $this->hasMany(Payment::class, 'transaction_id')->where('transaction_type', 'Debt'); }
}
