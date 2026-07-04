<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SalesItem extends Model
{
    use HasFactory;

    // Menentukan nama tabel secara eksplisit agar sesuai dengan migration (sale_items)
    protected $table = 'sale_items';

    // Konfigurasi primary key karena menggunakan tipe varchar/string
    protected $primaryKey = 'id';

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = [
        'id',
        'sale_id',
        'product_id',
        'sku',
        'product_name',
        'unit',
        'qty',
        'price',
        'cost',
        'subtotal',
        'is_wholesale',
    ];

    // Relasi ke model Sale
    public function sale()
    {
        return $this->belongsTo(Sales::class, 'sale_id', 'id');
    }

    // Relasi ke model Product
    public function product()
    {
        return $this->belongsTo(Product::class, 'product_id', 'id');
    }
}
