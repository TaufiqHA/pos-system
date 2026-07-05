<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Outlets extends Model
{
    protected $keyType = 'string';

    public $incrementing = false;

    protected $fillable = [
        'id',
        'branch_id',
        'name',
        'address',
        'phone',
    ];

    public function branch()
    {
        return $this->belongsTo(Branch::class, 'branch_id');
    }
}
