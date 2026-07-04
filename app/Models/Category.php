<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    use HasFactory;

    protected $table = 'categories';

    protected $primaryKey = 'id';

    public $incrementing = false; // Karena id menggunakan varchar (UUID)

    protected $keyType = 'string';

    protected $fillable = [
        'id',
        'name',
    ];

    // Relasi: Satu Kategori memiliki Banyak Produk
    public function products()
    {
        return $this->hasMany(Product::class, 'category_id', 'id');
    }
}
