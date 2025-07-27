<?php namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
class ReturnDetail extends Model {
    use HasFactory; protected $guarded = [];
    public function returnModel() { return $this->belongsTo(ReturnModel::class, 'return_id'); }
    public function product() { return $this->belongsTo(Product::class); }
}
