<?php

namespace App\View\Components;

use Illuminate\View\Component;
use App\Models\Inventory\Product;

class FilterStock extends Component
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
        $data['products'] = Product::where('id', request()->ft_product_id)->get();
        return view('components.filter-stock', $data);
    }
}
