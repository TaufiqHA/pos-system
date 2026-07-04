<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Suppliers extends Model
{
    use HasFactory;

    // Pengaturan primary key string
    protected $keyType = 'string';

    public $incrementing = false;

    // Field yang boleh diisi
    protected $fillable = [
        'id',
        'name',
        'contact_name',
        'phone',
        'email',
        'address',
        'notes',
    ];

    // TODO: Tambahkan relasi sesuai kebutuhan. Contoh:
    // public function purchases()
    // {
    //     return $this->hasMany(Purchases::class, 'supplier_id');
    // }
}
