<?php namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
class Production extends Model {
    use HasFactory; protected $guarded = [];
    public function finishedGood() { return $this->belongsTo(Product::class, 'finished_good_id'); }
    public function vendor() { return $this->belongsTo(Supplier::class, 'vendor_id'); }
    public function materials() { return $this->hasMany(ProductionMaterial::class); }
}
