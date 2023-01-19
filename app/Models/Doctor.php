<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;

class Doctor extends BaseModel
{
	use HasFactory, SoftDeletes;
    protected $guarded = ['id'];
    
    public function getLinkAttribute()
    {
        if ($this->status > 0 && can('UpdateDoctor')) { // will check permission
            return d_link(d_obj($this, ['name_en', 'name_kh']), route('setting.doctor.edit', [d_obj($this, 'id'), 'back' => url()->current()]));
        } else {
            return d_obj($this, ['name_en', 'name_kh']);
        }
    }

    function ecgs () {
        return $this->hasMany(Ecg::class, 'doctor_id');
    }

    function xrays () {
        return $this->hasMany(Xray::class, 'doctor_id');
    }

    function labors () {
        return $this->hasMany(Laboratory::class, 'doctor_id');
    }

    function echos () {
        return $this->hasMany(Echography::class, 'doctor_id');
    }

    function prescriptions () {
        return $this->hasMany(Prescription::class, 'doctor_id');
    }

    function invoices () {
        return $this->hasMany(Invoice::class, 'doctor_id');
    }
}
