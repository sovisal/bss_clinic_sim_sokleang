<x-filter :url="$url">
    <div class="col-sm-3 col-md-2">
        <x-form.daterangepicker
            name="ft_daterangepicker"
            value="{{ request()->ft_daterangepicker ?: (now()->startOfMonth()->format('d/M/Y') .' - '. now()->endOfMonth()->format('d/M/Y')) }}"
            drpStart="{{ request()->ft_daterangepicker_drp_start ?: now()->startOfMonth() }}"
            drpEnd="{{ request()->ft_daterangepicker_drp_end ?: now()->endOfMonth() }}"
            data-submit="#form-filter"
            label="Date"
        />
    </div>
    <div class="col-sm-3 col-md-2">
        <x-form.select name="ft_product_id" :url="route('inventory.product.index')" class="filter-input" label="{{ __('form.stock.product') }}">
            <option value="">{{ __('form.all') }}</option>
            @foreach ($products as $product)
            <option value="{{ $product->id }}" @selected($product->id == request()->ft_product_id)>{{ d_obj($product, ['name_en', 'name_kh']) }}</option>
            @endforeach
        </x-form.select>
    </div>
    {!! $slot !!}
</x-filter>