<?php namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
class ProductionMaterial extends Model {
    use HasFactory; protected $guarded = [];
    public function production() { return $this->belongsTo(Production::class); }
    public function rawMaterial() { return $this->belongsTo(Product::class, 'raw_material_id'); }
}
