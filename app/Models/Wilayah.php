<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Wilayah extends Model
{
    use HasFactory;

    protected $table = 'wilayahs';

    // Konfigurasi primary key varchar (string)
    protected $primaryKey = 'id';
    public $incrementing = false;
    protected $keyType = 'string';

    // Daftar kolom yang bisa diisi mass-assignment
    protected $fillable = [
        'id',
        'name',
    ];

    // TODO: Definisikan relasi ke model lain di sini sesuai kebutuhan
}
