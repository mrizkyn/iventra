<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory;

    protected $guarded = [];

    /**
     * Relasi ke detail penjualan
     */
    public function saleDetails()
    {
        return $this->hasMany(SaleDetail::class);
    }

    /**
     * Relasi ke penjualan (melalui detail)
     */
    public function sales()
    {
        return $this->hasManyThrough(Sale::class, SaleDetail::class, 'product_id', 'id', 'id', 'sale_id');
    }

    /**
     * Relasi ke detail pembelian
     */
    public function purchaseDetails()
    {
        return $this->hasMany(PurchaseDetail::class);
    }

    /**
     * Relasi ke pembelian (melalui detail)
     */
    public function purchases()
    {
        return $this->hasManyThrough(Purchase::class, PurchaseDetail::class, 'product_id', 'id', 'id', 'purchase_id');
    }

    /**
     * Relasi ke bahan produksi
     */
    public function productionMaterials()
    {
        return $this->hasMany(ProductionMaterial::class, 'raw_material_id');
    }
}
