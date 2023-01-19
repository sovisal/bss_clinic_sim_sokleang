<?php

namespace App\Models\Inventory;

use App\Models\BaseModel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;

class Product extends BaseModel
{
    use HasFactory, SoftDeletes;
    protected $guarded = ['id'];

    public function scopeFilter($query)
    {
        $query->when(request()->ft_category_id, function ($query, $category_id) {
            $query->where('category_id', '=', $category_id);
        });
        $query->when(request()->ft_type_id, function ($query, $type_id) {
            $query->where('type_id', '=', $type_id);
        });
        $query->when(request()->ft_status, function ($query, $status) {
            $query->where('qty_remain', (($status == 'remain') ? '>' : '<='), 0);
        });
    }

    public function category()
    {
        return $this->belongsTo(ProductCategory::class, 'category_id');
    }

    public function unit()
    {
        return $this->belongsTo(ProductUnit::class, 'unit_id');
    }

    public function type()
    {
        return $this->belongsTo(ProductType::class, 'type_id');
    }

    public function packages()
    {
        return $this->hasMany(ProductPackage::class, 'product_id');
    }

    public function suppliers()
    {
        return $this->belongsToMany(Supplier::class, 'supplier_id');
    }

    public function stockins()
    {
        return $this->hasMany(StockIn::class, 'product_id');
    }

    public function stock_outs()
    {
        return $this->hasMany(StockOut::class, 'product_id');
    }

    public function updateQty()
    {
        $stockins = $this->stockins()->get();

        $this->update([
            'qty_in' => $stockins->sum('qty_based'),
            'qty_out' => $stockins->sum('qty_used'),
            'qty_remain' => $stockins->sum('qty_remain'),
        ]);
        return $this;
    }

    public function getLinkAttribute()
    {
        if (($this->status ?? 0) > 0 && can('UpdateProduct')) { // will check permission
            return d_link(
                d_obj($this, ['name_en', 'name_kh']),
                route('inventory.product.edit', [d_obj($this, 'id'), 'back' => url()->current()])
            );
        } else {
            return d_obj($this, ['name_en', 'name_kh']);
        }
    }

    public function scopeOutOfStock($query)
    {
        return $query->whereRaw('qty_remain <= qty_alert');
    }

    public function scopeAvaiableStock($query)
    {
        if (env('STOCK_INVENTORY', false) == false) {
            return $query->where('status', '>=', '1');
        }
        return $query->where('status', '>=', '1')->where('qty_remain', '>', '0');
    }

    public function getAccuratePrice($unit_id = null)
    {
        if ($unit_id) {
            if ($unit_id != $this->unit_id) {
                $matched_package = $this->packages()->where('product_unit_id', $unit_id)->first();
                if ($matched_package && $matched_package->price) {
                    return $matched_package->price;
                }
            }
        }
        return $this->price;
    }

    public function getCalculationQty($unit_id = null)
    {
        if ($unit_id) {
            if ($unit_id != $this->unit_id) {
                $matched_package = $this->packages()->where('product_unit_id', $unit_id)->first();
                if ($matched_package && $matched_package->qty) {
                    return $matched_package->qty;
                }
            }
        }
        return 1;
    }

    public function validateStockExist($qty = 0, $unit_id = null)
    {
        if (env('STOCK_INVENTORY', false) == false) {
            return ['status' => true];
        }
        $qty_requested = $qty * $this->getCalculationQty($unit_id);

        return [
            'status' => $qty_requested <= $this->qty_remain,
            'errMsg' => 'Insufficient stock on product: ' . d_obj($this, ['name_kh', 'name_en']) . '! requested [' . d_number($qty_requested) . '] but available only [' . $this->qty_remain . ']',
            'sccMsg' => 'The quantity requested is avaiable.',
        ];
    }

    public function deductStock($qty = 0, $unit_id = null, $params)
    {

        if (env('STOCK_INVENTORY', false) == false) {
            return true;
        }

        // Prepare params for stockout creation
        $param_values = [];
        foreach (['type', 'date', 'document_no', 'product_id', 'price', 'note', 'total', 'parent_id'] as $field) {
            if ($params) {
                if (is_array($params) && !empty($params[$field])) {
                    $param_values[$field] = $params[$field];
                } elseif (is_object($params) && !empty($params->$field)) {
                    $param_values[$field] = $params->$field;
                } else {
                    $param_values[$field] = null;
                }
            }
        }

        $param_values['product_id'] = $this->id;
        $param_values['date'] = $param_values['date'] ?: date('Y-m-d');
        $param_values['price'] = $param_values['price'] ?: $this->getAccuratePrice($unit_id);
        $param_values['qty'] = $qty;
        $param_values['total'] = $param_values['qty'] * $param_values['price'];
        $param_values['qty_based'] = $requested_qty = $qty * $this->getCalculationQty($unit_id);;
        $param_values['unit_id'] = $unit_id ?: $this->unit_id;
        $stockOutCreated = StockOut::create($param_values);

        // Prepare for stockout detail
        foreach ($this->stockins()->where('qty_remain', '>', 0)->orderBy('date', 'ASC')->get() as $stockIn) {
            if ($stockIn->qty_remain >= $requested_qty) {
                // Case stock unit
                $stockOutCreated->stock_ins()->attach([$stockIn->id => ['qty' => $requested_qty]]);
                $stockIn->updateQty();
                break;
            } else {
                // Case stock separate
                $stockOutCreated->stock_ins()->attach([$stockIn->id => ['qty' => $stockIn->qty_remain]]);
                $requested_qty -= $stockIn->qty_remain;
                $stockIn->updateQty();
            }
        }

        $this->updateQty();
    }
}
