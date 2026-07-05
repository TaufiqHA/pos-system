<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Sales extends Model
{
    use HasFactory;

    // Karena id menggunakan tipe data varchar (string)
    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = [
        'id',
        'invoice',
        'branch_id',
        'user_id',
        'date',
        'subtotal',
        'discount',
        'tax',
        'grand_total',
        'status',
    ];

    // Relasi ke tabel branches
    public function branch()
    {
        return $this->belongsTo(Branch::class, 'branch_id');
    }

    // Relasi ke tabel users
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function salesItems()
    {
        return $this->hasMany(SalesItem::class, 'sale_id');
    }

    public function salesPayments()
    {
        return $this->hasMany(SalesPayment::class, 'sale_id');
    }
}
