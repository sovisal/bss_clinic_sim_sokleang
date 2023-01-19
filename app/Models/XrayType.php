<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;

class XrayType extends BaseModel
{
    use HasFactory, SoftDeletes;

    protected $guarded = ['id'];

    public function xrays()
    {
        return $this->hasMany(Xray::class, 'type_id');
    }
}
