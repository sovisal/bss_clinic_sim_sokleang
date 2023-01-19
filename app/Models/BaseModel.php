<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BaseModel extends Model
{
    use HasFactory;

    protected static function boot()
    {
        static::creating(function ($model) {
            if (Auth()->user()) {
                $model->user_id = Auth()->user()->id;
                $model->status = 1;
            }
        });

        parent::boot();
    }

    public function scopeParaFilter($query)
    {
        $query->when(request()->ft_daterangepicker_drp_start, function ($query, $daterangepicker_drp_start) {
            $query->where('requested_at', '>=', ($daterangepicker_drp_start . ' 00:00:00'));
        });
        $query->when(request()->ft_daterangepicker_drp_end, function ($query, $daterangepicker_drp_end) {
            $query->where('requested_at', '<=', ($daterangepicker_drp_end . ' 23:59:59'));
        });
        $query->when(request()->ft_patient_id, function ($query, $patient_id) {
            $query->where('patient_id', $patient_id);
        });
        $query->when(request()->ft_doctor_id, function ($query, $doctor_id) {
            $query->where('doctor_id', $doctor_id);
        });
        $query->when(request()->ft_status, function ($query, $status) {
            $query->where('status', (($status=='complete')? 2 : 1));
        });
        $query->when(request()->ft_payment_status, function ($query, $payment_status) {
            $query->where('payment_status', (($payment_status=='unpaid')? 0 : 1));
        });
    }

    public function scopeStockFilter($query)
    {
        $query->when(request()->ft_daterangepicker_drp_start, function ($query, $daterangepicker_drp_start) {
            $query->where('date', '>=', ($daterangepicker_drp_start));
        });
        $query->when(request()->ft_daterangepicker_drp_end, function ($query, $daterangepicker_drp_end) {
            $query->where('date', '<=', ($daterangepicker_drp_end));
        });
        $query->when(request()->ft_product_id, function ($query, $product_id) {
            $query->where('product_id', '=', $product_id);
        });
        $query->when(request()->ft_type, function ($query, $type) {
            $query->where('type', '=', $type);
        });
        $query->when(request()->ft_supplier_id, function ($query, $supplier_id) {
            $query->where('supplier_id', '=', $supplier_id);
        });
        $query->when(request()->ft_status, function ($query, $status) {
            if ($this->table == 'stock_ins') {
                $query->where('qty_remain', (($status == 'active') ? '>' : '<='), 0);
            }
        });
        $query->when(request()->ft_exp_status, function ($query, $status) {
            if ($this->table == 'stock_ins') {
                if ($status == 'active') {
                    $query->where(function ($query) {
                        $query->whereDate('exp_date', '>', date('Y-m-d'))->orWhereNull('exp_date');
                    });
                } else {
                    $query->whereDate('exp_date', '<=', date('Y-m-d'));
                }
            }
        });
    }

    public function scopeExclude($query, $value = [])
    {
        return $query->select(array_diff($this->columns, (array) $value));
    }

    // get Data Select2
    static function getSelect2($default_filter = [], $orderBy = [], $select = [])
    {
        $order_by = '';
        $orderBy = $orderBy ?: ['name', 'ASC'];
        if (is_array($orderBy[0])) {
            foreach ($orderBy as $order) {
                if ($order_by == '') {
                    $order_by = '`' . $order[0] . '` ' . $order[1];
                } else {
                    $order_by .= ', `' . $order[0] . '` ' . $order[1];
                }
            }
        } else {
            $order_by = '`' . $orderBy[0] . '` ' . $orderBy[1];
        }

        $select = ((is_array($select) && (count($select) > 1)) ? $select : ['id', 'name']);

        request()->merge($default_filter);
        $collection = parent::orderByRaw($order_by)
            ->select($select)
            ->limit(5)->get();
        $response = array();
        foreach ($collection as $item) {
            $temp = [];
            foreach ($select as $value) {
                $temp[$value] = $item->$value;
            }
            $response[] = $temp;
        }
        return response()->json($response);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function doctor()
    {
        return $this->belongsTo(Doctor::class);
    }

    public function doctor_requested()
    {
        return $this->belongsTo(Doctor::class, 'requested_by', 'id');
    }

    public function patient()
    {
        return $this->belongsTo(Patient::class);
    }

    public function gender()
    {
        return $this->belongsTo(DataParent::class, 'gender_id', 'id')->where('type', 'gender');
    }

    public function address()
    {
        return $this->belongsTo(Address_linkable::class);
    }

    public function payment()
    {
        return $this->belongsTo(DataParent::class, 'payment_type', 'id')->where('type', 'payment_type');
    }

    public function usage()
    {
        return $this->belongsTo(DataParent::class, 'usage_id', 'id');
    }

    public function scopeFilterTrashed($q)
    {
        $q->when(auth()->user()->isWebDev, function ($q) {
            return $q->withTrashed();
        });
    }

    static function getNextIndex($where_clause = [])
    {
        return (parent::select('index')->where($where_clause)->orderBy('index', 'desc')->first()->index ?? 0) + 1;
    }

    static function saveOrder($request)
    {
        if (is_array($request->ids) && count($request->ids) > 0) {
            $rows = parent::where('status', 1)->whereIn('id', $request->ids)->orderBy('index', 'asc')->get();
            foreach ($request->ids as $index => $id) {
                $rows->where('id', $id)->first()->update(['index' => ++$index]);
            }
        }
    }

    // Filter Attributes for Para clinic
    public function getfilterAttrAttribute()
    {
        return array_except(filter_unit_attr(unserialize($this->attribute) ?: []), ['patient_id', 'gender_id', 'age', 'doctor_id', 'status', 'amount', 'price', 'payment_type', 'address_id', 'pt_province_id', 'pt_district_id', 'pt_commune_id', 'pt_village_id', 'name_kh', 'name_en', 'index', 'requested_by']);
    }

    public function getTypeLinkAttribute()
    {
        if ($this->type && $this->type->status > 0) { // will check permission
            if ($this->table == 'echographies' && can('UpdateEchoType')) {
                return d_link(d_obj($this, 'type', ['name_en', 'name_kh']), route('setting.echo-type.edit', [d_obj($this, 'type', 'id'), 'back' => url()->current()]));
            }else if ($this->table == 'ecgs' && can('UpdateEcgType')) {
                return d_link(d_obj($this, 'type', ['name_en', 'name_kh']), route('setting.ecg-type.edit', [d_obj($this, 'type', 'id'), 'back' => url()->current()]));
            }else if ($this->table == 'xrays' && can('UpdateXRayType')) {
                return d_link(d_obj($this, 'type', ['name_en', 'name_kh']), route('setting.xray-type.edit', [d_obj($this, 'type', 'id'), 'back' => url()->current()]));
            }
        }

        return d_obj($this, 'type', ['name_en', 'name_kh']);
    }
}
