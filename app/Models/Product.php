<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Product extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'products';

    protected static function booted()
    {
        static::deleting(function ($product) {
            $product->productStocks()->delete();
            $product->wholesalePrices()->delete();
            $product->branchPrices()->delete();
        });
    }

    protected $primaryKey = 'id';

    public $incrementing = false; // Karena id menggunakan varchar (UUID)

    protected $keyType = 'string';

    protected $fillable = [
        'id',
        'category_id',
        'sku',
        'name',
        'description',
        'unit',
        'buy_price',
        'sell_price',
        'is_wholesale',
        'image',
    ];

    protected $casts = [
        'is_wholesale' => 'boolean',
        'buy_price' => 'decimal:2',
        'sell_price' => 'decimal:2',
    ];

    // Relasi: Produk dimiliki oleh satu Kategori
    public function category()
    {
        return $this->belongsTo(Category::class, 'category_id', 'id');
    }

    // Relasi: Produk memiliki banyak harga grosir
    public function wholesalePrices()
    {
        return $this->hasMany(WholesalePrice::class, 'product_id', 'id');
    }

    // Relasi: Produk memiliki banyak harga cabang
    public function branchPrices()
    {
        return $this->hasMany(ProductBranchPrices::class, 'product_id', 'id');
    }

    // Relasi ke model StockHistories (One to Many)
    public function stockHistories()
    {
        return $this->hasMany(StockHistories::class, 'product_id');
    }

    // Relasi ke model ProductStock (One to Many)
    public function productStocks()
    {
        return $this->hasMany(ProductStock::class, 'product_id');
    }
}
