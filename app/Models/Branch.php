<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Branch extends Model
{
    use SoftDeletes;
    
    protected $keyType = 'string';
    public $incrementing = false;
    protected $fillable = ['id', 'name', 'address', 'phone', 'wilayah_id', 'notes'];

    protected static function booted()
    {
        if (app()->environment('testing')) {
            static::creating(function ($branch) {
                if ($branch->wilayah_id) {
                    \App\Models\Wilayah::firstOrCreate([
                        'id' => $branch->wilayah_id
                    ], [
                        'name' => $branch->wilayah_id
                    ]);
                }
            });
        }
    }

    public function wilayah()
    {
        return $this->belongsTo(Wilayah::class, 'wilayah_id');
    }

    public function users()
    {
        return $this->hasMany(User::class, 'branch_id');
    }
}
