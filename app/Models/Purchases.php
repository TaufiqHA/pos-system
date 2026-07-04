<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Purchases extends Model
{
    protected $table = 'purchases';
    
    // Karena primary key menggunakan varchar (string)
    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'id',
        'invoice',
        'supplier_id',
        'branch_id',
        'user_id',
        'date',
        'subtotal',
        'discount',
        'tax',
        'grand_total',
        'status',
    ];

    // Definisikan Relasi
    public function supplier()
    {
        return $this->belongsTo(\App\Models\Suppliers::class, 'supplier_id');
    }

    public function branch()
    {
        return $this->belongsTo(\App\Models\Branch::class, 'branch_id');
    }

    public function user()
    {
        return $this->belongsTo(\App\Models\User::class, 'user_id');
    }

    public function items()
    {
        return $this->hasMany(\App\Models\PurchaseItem::class, 'purchase_id');
    }

    public function purchasePayments()
    {
        return $this->hasMany(\App\Models\PurchasePayment::class, 'purchase_id');
    }
}
