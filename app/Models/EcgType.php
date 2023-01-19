<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;

class EcgType extends BaseModel
{
    use HasFactory, SoftDeletes;

    protected $guarded = ['id'];

    public function ecgs()
    {
        return $this->hasMany(Ecg::class, 'type_id');
    }
}
