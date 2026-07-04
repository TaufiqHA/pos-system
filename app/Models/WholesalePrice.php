<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class WholesalePrice extends Model
{
    use HasFactory;

    protected $table = 'wholesale_prices';

    // Konfigurasi primary key string
    protected $primaryKey = 'id';

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = [
        'product_id',
        'branch_id',
        'min_qty',
        'price',
    ];

    // Otomatisasi pembuatan UUID saat insert
    protected static function boot()
    {
        parent::boot();
        static::creating(function ($model) {
            if (empty($model->{$model->getKeyName()})) {
                $model->{$model->getKeyName()} = (string) Str::uuid();
            }
        });
    }

    // Relasi ke tabel products
    public function product()
    {
        return $this->belongsTo(Product::class, 'product_id', 'id');
    }

    // Relasi ke tabel branches
    public function branch()
    {
        return $this->belongsTo(Branch::class, 'branch_id', 'id');
    }
}
