<?php

namespace App\View\Components;

use Illuminate\View\Component;
use App\Models\Inventory\ProductType;
use App\Models\Inventory\ProductCategory;

class FilterProduct extends Component
{

    public $url;

    /**
     * Create a new component instance.
     */
    public function __construct($url = '')
    {
        $this->url = $url;
    }

    /**
     * Get the view / contents that represent the component.
     */
    public function render()
    {
        $data['categories'] = ProductCategory::where('status', 1)->orderBy('name_en', 'asc')->get();
        $data['types'] = ProductType::where('status', 1)->orderBy('name_en', 'asc')->get();
        return view('components.filter-product', $data);
    }
}
