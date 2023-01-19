<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class LaborItem extends BaseModel
{
	use HasFactory, SoftDeletes;
	protected $guarded = ['id'];

	public function type()
	{
		return $this->belongsTo(LaborType::class, 'type_id');
    }

    public function labor_details()
    {
        return $this->hasMany(LaborDetail::class, 'labor_item_id');
    }
}
