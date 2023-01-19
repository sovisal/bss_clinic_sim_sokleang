<?php

namespace App\Models\Inventory;

use App\Models\BaseModel;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Supplier extends BaseModel
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
            $query->where('status', (($status == 'active') ? 1 : 0));
        });
    }

    public function category()
    {
        return $this->belongsTo(ProductCategory::class, 'category_id');
    }

    public function stockins()
    {
        return $this->hasMany(StockIn::class, 'supplier_id');
    }

    public function type()
    {
        return $this->belongsTo(ProductType::class, 'type_id');
    }

    // public function products()
    // {
    //     return $this->belongsToMany(Product::class, 'supplier_id')->withTimestamps();
    // }
    
    public function assignProduct($products = [])
    {
        $this->hasAbilities()->sync($products);
    }

    public function getLinkAttribute()
    {
        if ($this->status > 0 && can('UpdateSupplier')) { // will check permission
            return d_link(d_obj($this, ['name_en', 'name_kh']), route('inventory.supplier.edit', [d_obj($this, 'id'), 'back' => url()->current()]));
        } else {
            return d_obj($this, ['name_en', 'name_kh']);
        }
    }
}
