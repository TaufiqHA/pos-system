<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

use Illuminate\Database\Eloquent\Factories\HasFactory;

class PurchasePayment extends Model
{
    use HasFactory;

    // Set tipe primary key menjadi string karena kita pakai varchar
    protected $keyType = 'string';
    public $incrementing = false;

    // Field yang boleh diisi
    protected $fillable = [
        'id',
        'purchase_id',
        'method',
        'amount',
        'status',
        'reference',
        'paid_at',
    ];

    // Relasi ke tabel purchases
    public function purchase()
    {
        // Pastikan Anda memanggil class model yang benar untuk purchases
        return $this->belongsTo(Purchases::class, 'purchase_id'); 
    }
}
