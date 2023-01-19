<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;

class EchoType extends BaseModel
{
    use HasFactory, SoftDeletes;

    protected $guarded = ['id'];

    public function echos()
    {
        return $this->hasMany(Echography::class, 'type_id');
    }
}
