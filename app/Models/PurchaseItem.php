<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PurchaseItem extends Model
{
    protected $table = 'purchase_items';

    // Konfigurasi Primary Key tipe String (Varchar)
    protected $keyType = 'string';

    public $incrementing = false;

    protected $fillable = [
        'id',
        'purchase_id',
        'product_id',
        'sku',
        'product_name',
        'unit',
        'qty',
        'price',
        'subtotal',
    ];

    // Relasi ke Model Purchases
    public function purchase()
    {
        return $this->belongsTo(Purchases::class, 'purchase_id');
    }

    // Relasi ke Model Product
    public function product()
    {
        return $this->belongsTo(Product::class, 'product_id');
    }
}
